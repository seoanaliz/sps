<?php
    Package::Load( 'SPS.Site' );

    /**
     * SyncTop Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SyncTop {

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $source = SourceFeedFactory::GetOne(array('externalId' => ParserVkontakte::TOP));

            if (empty($source)) {
                return;
            }

            $parser = new ParserTop();

            $tries  = 3;
            $i      = 0;

            while ($i < $tries) {
                $i++;
                Logger::Info('Try number ' . $i);

                try {
                    $posts = $parser->get_top();

                    $this->saveFeedPosts($source, $posts);

                    break;
                } catch (Exception $Ex) {
                    AuditUtility::CreateEvent('importErrors', 'feed', $source->externalId, $Ex->getMessage());
                }
            }
        }

        /**
         * Метод синхронизации постов
         * @param SourceFeed    $source лента откуда получили посты
         * @param array         $posts массив постов
         */
        private function saveFeedPosts($source, $posts) {
            /**
             * Ищем в базе уже возможно сохраненные
             * Попутно собираем id тех, которые надо пропустить
             */
            $externalIds    = array();

            foreach ($posts as $post) {
                $externalId = TextHelper::ToUTF8($post['id']);
                $externalIds[] = $externalId;
            }

            //ищем $externalIds
            $__mapping = ArticleFactory::$mapping;
            ArticleFactory::$mapping['view'] = 'articles';
            $originalObjects = ArticleFactory::Get(
                array('_externalId' => $externalIds, 'sourceFeedId' => $source->sourceFeedId)
                , array(
                    BaseFactory::WithColumns => '"articleId", "externalId"'
                    , BaseFactory::WithoutPages => true
                    , BaseFactory::WithoutDisabled => false
                )
            );
            ArticleFactory::$mapping = $__mapping;

            if (!empty($originalObjects)) {
                $originalObjects = BaseFactoryPrepare::Collapse($originalObjects, 'externalId');
            }

            /**
             * Обходим посты и созраняем их в бд, попутно сливая фотки
             */
            foreach ($posts as $post) {
                $externalId = TextHelper::ToUTF8($post['id']);

                if (!empty($originalObjects[$externalId])) {
                    continue; //не сохраняем то что уже сохранили
                }

                $article = new Article();
                $article->sourceFeedId  = $source->sourceFeedId;
                $article->externalId    = $externalId;
                $article->createdAt     = DateTimeWrapper::Now();
                $article->importedAt    = DateTimeWrapper::Now();
                $article->statusId      = 1;

                $articleRecord = new ArticleRecord();
                $articleRecord->content = $post['text'];
                $articleRecord->likes   = Convert::ToInteger($post['likes']);
                $articleRecord->photos  = array();

                //сохраняем фотки на медиа сервер
                if (!empty($post['photo'])) {
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
                $url = str_replace('https://', 'http://', $photo['url']);
                file_put_contents($tmpName, file_get_contents($url));
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