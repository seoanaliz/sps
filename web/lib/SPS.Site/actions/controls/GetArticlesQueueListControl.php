<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticlesQueueListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticlesQueueListControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $timestamp = Request::getInteger( 'timestamp' );
            $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);

            $type = Request::getString('type');
            if (empty($type) || empty(GridLineUtility::$Types[$type])) {
                $type = GridLineUtility::TYPE_ALL;
            }

            $queueDate  = new DateTimeWrapper($date);
            $today      = new DateTimeWrapper(date('d.m.Y'));
            $isHistory  = ($queueDate < $today);

            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $targetFeed   = TargetFeedFactory::GetById($targetFeedId);

            $articleRecords = array();
            $articlesQueue  = array();

            //check access
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                $targetFeedId = null;
            }

            if(!empty($targetFeedId) && !empty($targetFeed)) {
                //вытаскиваем всю очередь на этот день на этот паблик
                $articlesQueue = ArticleQueueFactory::Get(
                    array(
                        'targetFeedId' => $targetFeedId
                        , 'startDateAsDate' => $date
                        , 'type' => ($type == GridLineUtility::TYPE_ALL) ? null : $type
                    )
                    , array(
                        BaseFactory::WithoutPages => true
                        , BaseFactory::OrderBy => ' "sentAt" DESC, "startDate" DESC '
                    )
                );

                if(!empty($articlesQueue)) {
                    //load arciles data
                    $articleRecords = ArticleRecordFactory::Get(
                        array('_articleQueueId' => array_keys($articlesQueue))
                    );
                    if (!empty($articleRecords)) {
                        $articleRecords = BaseFactoryPrepare::Collapse($articleRecords, 'articleQueueId', false);
                    }
                }
            }

            Response::setArray( 'articleRecords',   $articleRecords );
            Response::setArray( 'articlesQueue',    $articlesQueue );

            if ($isHistory) {
                Page::$TemplatePath = 'tmpl://fe/elements/arcticles-queue-history.tmpl.php';
            } else if ($type == GridLineUtility::TYPE_ALL) {
                Page::$TemplatePath = 'tmpl://fe/elements/arcticles-queue-view.tmpl.php';
            } else {
                $this->setGrid($targetFeedId, $date, $type, $articlesQueue);
            }
        }

        private function setGrid($targetFeedId, $date, $type, $articlesQueue) {
            $now = DateTimeWrapper::Now();

            $grid = GridLineUtility::GetGrid($targetFeedId, $date, $type);

            if(!empty($articlesQueue)) {
                foreach($articlesQueue as $articlesQueueItem) {
                    //ищем место в grid для текущей $articlesQueueItem
                    $place = null;
                    foreach ($grid as $key => $gridItem) {
                        if ($gridItem['dateTime'] >= $articlesQueueItem->startDate && $gridItem['dateTime'] <= $articlesQueueItem->endDate) {
                            if (empty($gridItem['queue'])) {
                                $place = $key;
                            }
                        }
                    }

                    if ($place !== null) {
                        $grid[$place]['queue'] = $articlesQueueItem;
                        $grid[$place]['blocked'] = ($articlesQueueItem->statusId != 1 || $articlesQueueItem->endDate <= $now);
                        $grid[$place]['failed'] = ($articlesQueueItem->statusId != StatusUtility::Finished && $articlesQueueItem->endDate <= $now);
                    }
                }
            }

            Response::setArray( 'grid', $grid );
        }
    }
?>