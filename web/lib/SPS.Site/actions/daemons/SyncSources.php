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
         * Один вызов этого метода просинхронизирует только одн страницу одного sourceFeed из-за длительности работы
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $this->daemon                   = new Daemon();
            $this->daemon->package          = 'SPS.Site';
            $this->daemon->method           = 'SyncSources';
            $this->daemon->maxExecutionTime = '00:10:00';

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
                    $this->daemon->name = "source$source->sourceFeedId::page$targetPage";
                    if ( !$this->daemon->Lock() ) {
                        Logger::Warning( "Failed to lock {$this->daemon->name}");
                        continue; //переходим к следующему sorce
                    }

                    try {
                        $posts = $parser->get_posts($targetPage);
                    } catch (Exception $Ex) {
                        continue; //переходим к следующему sorce
                    }

                    $result = $this->saveFeedPosts($source, $posts);

                    if ($result) {
                        //обновляем pagesCountProcessed в базе
                        $source->processed = $pagesCountProcessed + 1;
                        SourceFeedFactory::UpdateByMask($source, array('processed'), array('sourceFeedId' => $source->sourceFeedId));
                    }

                    //снимаем лок
                    $this->daemon->Unlock();
                }
            }
        }

        private function saveFeedPosts($source, $posts) {
            foreach ($posts as $post) {
                $externalId = TextHelper::ToUTF8($post['id']);

                //TODO написать одним запросом
                $originalObject = ArticleFactory::Get(
                    array('externalId' => $externalId, 'sourceFeedId' => $source->sourceFeedId)
                    , array(BaseFactory::WithColumns => '"articleId"')
                );

                if (!empty($originalObject)) {
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
                if (!empty($post['photo'])) {
                    foreach ($post['photo'] as $photo) {
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
                            $articleRecord->photos[] = array(
                                'filename' => $fileUploadResult['filename'],
                                'title' => !empty($photo['descr']) ? TextHelper::ToUTF8($photo['descr']) : ''
                            );
                        }
                    }
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

            //TODO возвращать true только если полностью обработали страницу и к ней не вернемся
            return true;
        }
    }
?>