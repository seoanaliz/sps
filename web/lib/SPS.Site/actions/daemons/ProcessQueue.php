<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );

    /**
     * ProcessQueue Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ProcessQueue {

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            ConnectionFactory::BeginTransaction();

            //атомарно занимаем запись в очереди
            //берем только ту запись, которую нужно отправить по времени
            //валера, твое время настало!
            $sql = <<<sql
                SELECT * FROM "articleQueues"
                WHERE "statusId" = 1
                AND @now >= "startDate"
                AND @now <= "endDate"
                LIMIT 1 FOR UPDATE;
sql;

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
            $cmd->SetDateTime('@now', DateTimeWrapper::Now());

            $ds         = $cmd->Execute();
            $structure  = BaseFactory::getObjectTree( $ds->Columns );
            while ($ds->next() ) {
                $object = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
            }

            if (!empty($object)) {
                $object->statusId = StatusUtility::Queued;
                ArticleQueueFactory::UpdateByMask($object, array('statusId'), array('articleQueueId' => $object->articleQueueId) );
                ConnectionFactory::CommitTransaction(true);
            } else {
                ConnectionFactory::CommitTransaction(true);
                return;
            }

            $this->sendArticleQueue($object);
        }

        /**
         * @param ArticleQueue $articleQueue
         * @return bool
         */
        private function sendArticleQueue($articleQueue) {
            $result = false;
            //select objects
            $targetFeed = TargetFeedFactory::GetById($articleQueue->targetFeedId, array(), array(BaseFactory::WithLists => true));
            $articleRecord = ArticleRecordFactory::GetOne(
                array('articleQueueId' => $articleQueue->articleQueueId)
            );

            //check for article options
            ArticleFactory::$mapping['view'] = ArticleFactory::$mapping['table'];
            $article = ArticleFactory::GetById($articleQueue->articleId, array(), array(BaseFactory::WithoutDisabled => false));
            $sourceFeed = SourceFeedFactory::GetOne(array('sourceFeedId' => $article->sourceFeedId));
            $sourceFeed = !empty($sourceFeed) ? $sourceFeed : new SourceFeed();

            if ($targetFeed->type == TargetFeedUtility::FB) {
                if (empty($targetFeed) || empty($articleRecord)) {
                    return false;
                }

                $this->sendPostToFb($targetFeed, $articleQueue, $articleRecord);
            }

            if ($targetFeed->type == TargetFeedUtility::VK ) {
                if (empty($targetFeed)  || empty($articleRecord)) {
                    return false;
                }
                $roles = array();

                //из-за вавилонства с ролями с наших пабликов пока могут посылать только editors
                if ( !$targetFeed->isOur ) {
                    $roles = array( UserFeed::ROLE_OWNER, UserFeed::ROLE_EDITOR);
                }
                $tokens = AccessTokenUtility::getAllTokens( $targetFeed->targetFeedId, AuthVkontakte::$Version, $roles );
                //отправка в ненаши - только с токена запланировавшего пост
                if ( !$targetFeed->isOur ) {
                    if( isset($tokens[$articleQueue->author] )) {
                        $tokens = array( $tokens[$articleQueue->author]);
                    } else {
                        $tokens = array();
                    }
                } else {
                    shuffle( $tokens );
                }

                foreach ($tokens as $token) {
                    try {
                        $this->sendPostToVk($sourceFeed, $targetFeed, $articleQueue, $articleRecord, $token, $article);
                        return true;
                    } catch (Exception $Ex) {
                    }
                }
                $this->restartArticleQueue($articleQueue);
                $err = 'Failed to post, persumably publishers are banned, public id = ' . $targetFeed->externalId;
                Logger::Warning($err);

                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);
                return true;
            }
        }

        /**
         * @param SourceFeed $sourceFeed
         * @param TargetFeed $targetFeed
         * @param ArticleQueue $articleQueue
         * @param ArticleRecord $articleRecord
         * @param Article $article
         */
        private function sendPostToVk($sourceFeed, $targetFeed, $articleQueue, $articleRecord, $token, $article) {
            $isWithSmallPhoto = ArticleUtility::IsTopArticleWithSmallPhoto($sourceFeed, $articleRecord);
            if ($isWithSmallPhoto) {
                $articleRecord->photos = array();
            }

            $link = $articleRecord->link;
            if (strpos($link, 'topface.com') !== false) {
                $link .= '?ref=pub';
            }
            $post_data = array(
                'text' => $articleRecord->content,
                'group_id' => $targetFeed->externalId,
                'vk_access_token' => $token,
                'photo_array' => array(),
                'audio_id' => array(),
                'video_id' => array(),
                'link' => $link,
                'header' => '',
                'repost_post' => $articleRecord->repostExternalId
            );

            if (!empty($articleRecord->photos)) {
                /**
                 * отправляем не более 10 фоток
                 */
                $articlePhotos = array_slice($articleRecord->photos, 0, 10);
                foreach ($articlePhotos as $photoItem) {
                    $remotePath = MediaUtility::GetArticlePhoto($photoItem);
                    $localPath  = Site::GetRealPath('temp://upl_' . md5($remotePath) . '.jpg');

                    file_put_contents($localPath, file_get_contents($remotePath));

                    $post_data['photo_array'][] = $localPath;
                }
            }

            $sender = new SenderVkontakte($post_data);

            try {
                if ( !$post_data['repost_post'] ) {
                    $articleQueue->externalId = $sender->send_post();
                } else {
                    $articleQueue->externalId = $sender->repost();
                }
                //закрываем
                $this->finishArticleQueue($articleQueue);

                if( $article->isSuggested ) {
                    try {
                        $sender->delete_post( $article->externalId );
                    } catch( Exception $e ) {
                        AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                            'cant delete post (' . $article->externalId . ' ) ' .  $e->getMessage() );

                    }
                }

                if ($article->sourceFeedId == SourceFeedUtility::FakeSourceTopface) {
                    TopfaceUtility::AcceptPost($article, $articleRecord, $articleQueue->externalId);
                }
            } catch (ChangeSenderException $Ex){
                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                    'failed to post from token' . $token . ' ' . $Ex->getMessage());
                throw $Ex;
            } catch (Exception $Ex){
                $err = $Ex->getMessage();
                Logger::Warning($err);
                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);
                throw $Ex;
            }

            //unlink temp files
            if (!empty($post_data['photo_array'])) {
                foreach ($post_data['photo_array'] as $localPath) {
                    @unlink($localPath);
                }
            }

        }

        /**
         * @param TargetFeed $targetFeed
         * @param ArticleQueue $articleQueue
         * @param ArticleRecord $articleRecord
         */
        private function sendPostToFb($targetFeed, $articleQueue, $articleRecord) {
            $fields_arr = array(
                'album'         => $targetFeed->externalId,
                'access_token'  => $targetFeed->params['token'],
                'source'        => null,
                'message'       => $articleRecord->content,
                'targeting'     => array()
            );

            if (!empty($articleRecord->photos)) {
                $photoItem = current($articleRecord->photos);

                $remotePath = MediaUtility::GetArticlePhoto($photoItem);
                $localPath  = Site::GetRealPath('temp://upl_' . md5($remotePath) . '.jpg');

                file_put_contents($localPath, file_get_contents($remotePath));

                $fields_arr['source'] = '@' . $localPath;
            } else {
                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, 'Article has no photos');
                return;
            }

            try {
                $sender = new SenderFacebook(json_encode($fields_arr));
                $result = $sender->send_photo();
                Logger::Info($result);

                //закрываем
                $this->finishArticleQueue($articleQueue);
            } catch (Exception $Ex) {
                $err = $Ex->getMessage();
                Logger::Warning($err);

                AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);

                //ставим обратно в очередь
                $this->restartArticleQueue($articleQueue);
            }

            //unlink temp files
            @unlink($localPath);
        }

        private function finishArticleQueue($articleQueue) {
            $articleQueue->statusId = StatusUtility::Finished;
            $articleQueue->sentAt = DateTimeWrapper::Now();
            ArticleQueueFactory::UpdateByMask($articleQueue, array('statusId', 'sentAt', 'externalId'), array('articleQueueId' => $articleQueue->articleQueueId) );

            $object = ArticleFactory::GetById($articleQueue->articleId, array(), array(BaseFactory::WithoutDisabled => false));

            if (!empty($object->authorId)) {
                $object->queuedAt = null;
                $object->sentAt = DateTimeWrapper::Now();
                ArticleFactory::UpdateByMask($object, array('sentAt', 'queuedAt'), array('articleId' => $object->articleId));
                AuthorEventUtility::EventSent($object);
            }
        }

        private function restartArticleQueue($articleQueue) {
            $articleQueue->statusId = 1;
            ArticleQueueFactory::UpdateByMask($articleQueue, array('statusId'), array('articleQueueId' => $articleQueue->articleQueueId) );
        }
    }
?>
