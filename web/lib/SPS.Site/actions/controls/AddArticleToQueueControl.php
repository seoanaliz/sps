<?php
    Package::Load( 'SPS.Site' );

    /**
     * AddArticleToQueueControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class AddArticleToQueueControl {

        private function buildDates($object, $timestamp) {
            $object->startDate = new DateTimeWrapper(date('r', $timestamp));
            $object->endDate = new DateTimeWrapper(date('r', $timestamp));

            $object->startDate->modify('-5 minutes');
            $object->endDate->modify('+15 minutes');
        }

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            $articleId = Request::getInteger( 'articleId' );
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $timestamp = Request::getInteger( 'timestamp' );
            $queueId = Request::getInteger( 'queueId' );
            $type = Request::getString('type');

            if (empty($articleId) || empty($targetFeedId) || empty($timestamp) || empty($type) || empty(GridLineUtility::$Types[$type])) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            if (!empty($queueId)) {
                //просто перемещаем элемент очереди
                ArticleUtility::ChangeQueueDates($queueId, $timestamp);

                $result = array(
                    'success' => true,
                    'id' => $queueId
                );
                echo ObjectHelper::ToJSON($result);
                return true;
            }

            $article = ArticleFactory::GetById($articleId);
            $targetFeed = TargetFeedFactory::GetById($targetFeedId);
            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));

            if (empty($article) || empty($targetFeed) || empty($articleRecord)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //check access
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId) || !AccessUtility::HasAccessToSourceFeedId($article->sourceFeedId)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //source feed
            $sourceFeed = SourceFeedFactory::GetById($article->sourceFeedId);

            if ($sourceFeed->type != SourceFeedUtility::Ads) {
                //проверяем, если ли такая $articleId в этой $targetFeedId
                $existsCount = ArticleQueueFactory::Count(
                    array('articleId' => $articleId, 'targetFeedId' => $targetFeedId)
                );

                if ($existsCount) {
                    $result['message'] = 'articleQueueExists';
                    echo ObjectHelper::ToJSON($result);
                    return false;
                }
            }

            $object = new ArticleQueue();
            $object->createdAt = DateTimeWrapper::Now();
            $object->articleId = $article->articleId;
            $object->targetFeedId = $targetFeed->targetFeedId;
            $object->type = $type;
            $object->author = AuthVkontakte::IsAuth();
            ArticleUtility::BuildDates($object, $timestamp);

            $object->statusId = 1;

            $articleQueueRecord = clone $articleRecord;
            $articleQueueRecord->articleRecordId = null;
            $articleQueueRecord->articleId = null;

            ConnectionFactory::BeginTransaction();
            
            $sqlResult = ArticleQueueFactory::Add($object);

            if ($sqlResult) {
                $articleQueueRecord->articleQueueId = ArticleQueueFactory::GetCurrentId();
                
                $sqlResult = ArticleRecordFactory::Add($articleQueueRecord);
            }

            ConnectionFactory::CommitTransaction($sqlResult);

            if ($sqlResult) {
                $result = array(
                    'success' => true,
                    'id' => $articleQueueRecord->articleQueueId
                );

                $sourceFeed = SourceFeedFactory::GetById($article->sourceFeedId);
                if ($sourceFeed->type != SourceFeedUtility::Ads) {
                    //блокируем статью, чтобы ее больше никто не пытался отправить
                    $o = new Article();
                    $o->statusId = 2;
                    ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $article->articleId));

                    $result['moved'] = true;
                } else {
                    $result['moved'] = false;
                }
            }
            echo ObjectHelper::ToJSON($result);
        }
    }
?>