<?php
    Package::Load( 'SPS.Site' );

    /**
     * AddAricleToQueueControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class AddAricleToQueueControl {

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

            if (empty($articleId) || empty($targetFeedId) || empty($timestamp)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $article = ArticleFactory::GetById($articleId);
            $targetFeed = TargetFeedFactory::GetById($targetFeedId);
            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));

            if (empty($article) || empty($targetFeed) || empty($articleRecord)) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //TODO проверяем, если ли такая $articleId в этой $targetFeedId

            $dateTime = new DateTimeWrapper(date('r', $timestamp));

            $object = new ArticleQueue();
            $object->createdAt = DateTimeWrapper::Now();
            $object->articleId = $article->articleId;
            $object->targetFeedId = $targetFeed->targetFeedId;
            $object->startDate = new DateTimeWrapper(date('r', $timestamp));
            $object->endDate = new DateTimeWrapper(date('r', $timestamp));

            $object->startDate->modify('-10 minutes');
            $object->endDate->modify('+10 minutes');

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
            }

            echo ObjectHelper::ToJSON($result);
        }
    }
?>