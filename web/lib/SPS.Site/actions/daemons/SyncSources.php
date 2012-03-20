<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );
    Package::Load( 'SPS.VK' );

    /**
     * SyncSources Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SyncSources {

        /**
         * @var Daemon
         */
        private $daemon;

        /**
         * Один вызов этого метода просинхронизирует только одну страницу каждого sourceFeed
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $this->daemon                   = new Daemon();
            $this->daemon->package          = 'SPS.Site';
            $this->daemon->method           = 'SyncSources';
            $this->daemon->maxExecutionTime = '01:00:00';

            //get sources
            $sources = SourceFeedFactory::Get();
            foreach ($sources as $source) {
                //инитим парсер
                $parser = new ParserVkontakte($source->externalId);

                try {
                    $count = $parser->get_posts_count();
                } catch (Exception $Ex) {
                    break;
                }

                $pagesCountTotal = ceil($count / ParserVkontakte::PAGE_SIZE);

                $pagesCountProcessed = Convert::ToInt($source->processed);
                //если кол-во обработанных страниц в source меньше $pagesCount - работаем
                if ($pagesCountTotal > $pagesCountProcessed) {
                    //парсим одну нужную страницу
                    $targetPage = $pagesCountTotal - 1 - $pagesCountProcessed;

                    //пытаемся залочиться
                    $this->daemon->name = "source$source->externalId";
                    if ( !$this->daemon->Lock() ) {
                        Logger::Warning( "Failed to lock {$this->daemon->name}");
                        continue; //переходим к следующему sorce
                    }

                    try {
                        $posts = $parser->get_posts($targetPage);
                    } catch (Exception $Ex) {
                        continue; //переходим к следующему sorce
                    }

                    $posts = !empty($posts) ? $posts : array();

                    $this->saveFeedPosts($source, $posts);
                }
            }
        }

        /**
         * Метод синхронизации постов
         * @param SourceFeed    $source лента откуда получили посты
         * @param array         $posts массив постов
         * @return bool нужно ли обновить processed у $source
         */
        private function saveFeedPosts($source, $posts) {
            /**
             * Ищем в базе уже возможно сохраненные
             */
            $externalIds = array();
            foreach ($posts as $post) {
                $externalIds[] = TextHelper::ToUTF8($post['id']);
            }

            $originalObjects = ArticleFactory::Get(
                array('_externalId' => $externalIds, 'sourceFeedId' => $source->sourceFeedId)
                , array(BaseFactory::WithColumns => '"articleId", "externalId"', BaseFactory::WithoutPages => true)
            );
            if (!empty($originalObjects)) {
                $originalObjects = BaseFactoryPrepare::Collapse($originalObjects, 'externalId');
            }

            /**
             * TODO ставить $hasSkippedPosts = true если по каким-то условиям не сохраняем все посты
             */
            $hasSkippedPosts = false;
            if (!$hasSkippedPosts) {
                //обновляем pagesCountProcessed в базе
                $source->processed = Convert::ToInt($source->processed) + 1;
                SourceFeedFactory::UpdateByMask($source, array('processed'), array('sourceFeedId' => $source->sourceFeedId));
            }

            //снимаем лок
            $this->daemon->Unlock();

            /**
             * Обходим посты и созраняем их в бд, попутно сливая фотки
             */
            foreach ($posts as $post) {
                $externalId = TextHelper::ToUTF8($post['id']);

                if (!empty($originalObjects[$externalId])) {
                    continue; //не сохраняем то что уже сохранили
                }

                $article = new Article();
                $article->sourceFeedId = $source->sourceFeedId;
                $article->externalId = $externalId;
                $article->createdAt = DateTimeWrapper::Now(); //todo взять дату из поста когда будет
                $article->importedAt = DateTimeWrapper::Now();
                $article->statusId = 1;

                $articleRecord = new ArticleRecord();
                $articleRecord->content = TextHelper::ToUTF8($post['text']);
                $articleRecord->likes   = Convert::ToInteger($post['likes']);
                $articleRecord->photos  = array();

                //сохраняем фотки на медиа сервер
                if (!empty($post['photo']) && !Site::IsDevel()) {
                    $articleRecord->photos = $this->savePostPhotos($post['photo']);
                }

                //сохраняем в транзакции
                $conn = ConnectionFactory::Get();
                $conn->begin();

                $result = ArticleFactory::Add($article);

                if ( $result ) {
                    $articleRecord->articleId = ArticleFactory::GetCurrentId();
                    $result = ArticleRecordFactory::Add( $articleRecord );
                }

                if ( $result ) {
                    $conn->commit();
                } else {
                    $conn->rollback();
                }
            }
        }

        /**
         * Метод сохраняет фотки извне на медиасервер и готовит инфу о них для записи в базу
         * @param $data
         * @return array
         */
        private function savePostPhotos($data) {
            $result = array();

            foreach ($data as $photo) {
                //moving photo to local temp
                $tmpName = Site::GetRealPath('temp://') . md5($photo['url']) . '.jpg';
                file_put_contents($tmpName, file_get_contents($photo['url']));
                $file = array(
                    'tmp_name'  => $tmpName,
                    'name'      => $tmpName,
                );
                $fileUploadResult = MediaUtility::SaveTempFile( $file, 'Article', 'photos' );

                if( !empty( $fileUploadResult['filename'] ) ) {
                    MediaUtility::MoveObjectFilesFromTemp( 'Article', 'photos', array($fileUploadResult['filename']) );
                    unlink($tmpName);

                    $result[] = array(
                        'filename' => $fileUploadResult['filename'],
                        'title' => !empty($photo['desc']) ? TextHelper::ToUTF8($photo['desc']) : ''
                    );
                }
            }

            return $result;
        }
    }
?>