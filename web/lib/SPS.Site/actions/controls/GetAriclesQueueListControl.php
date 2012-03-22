<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetAriclesQueueListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetAriclesQueueListControl {

        private function getGrid($date) {
            //generate table
            $result = array();

            for ($i = 24; $i >= 9; $i--) {
                $queueDate = new DateTimeWrapper($date);
                $queueDate->modify('+ ' . $i . 'hours');
                if ($i == 24) {
                    $queueDate->modify('-1 minute');
                }

                $result[] = array(
                    'dateTime' => $queueDate
                );
            }

            return $result;
        }

        /**
         * Entry Point
         */
        public function Execute() {
            $timestamp = Request::getInteger( 'timestamp' );
            if ($timestamp) {
                $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);
            } else {
                $date = date('d.m.Y');
            }

            $grid = $this->getGrid($date);

            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $targetFeed   = TargetFeedFactory::GetById($targetFeedId);

            $articleRecords = array();

            if(!empty($targetFeedId) && !empty($targetFeed)) {
                //вытаскиваем всю очередь на этот день на этот паблик
                $articlesQueue = ArticleQueueFactory::Get(
                    array('targetFeedId' => $targetFeedId, 'startDateAsDate' => $date)
                    , array(BaseFactory::WithoutPages => true)
                );

                if(!empty($articlesQueue)) {
                    foreach($articlesQueue as $articlesQueueItem) {
                        //ищем место в grid для текущей $articlesQueueItem
                        foreach ($grid as &$gridItem) {
                            if ($gridItem['dateTime'] >= $articlesQueueItem->startDate && $gridItem['dateTime'] <= $articlesQueueItem->endDate) {
                                if (empty($gridItem['queue'])) {
                                    $gridItem['queue'] = $articlesQueueItem;
                                    $gridItem['canDelete'] = ($articlesQueueItem->statusId == 1);
                                }
                            }
                        }
                    }

                    //load arciles data
                    $articleRecords = ArticleRecordFactory::Get(
                        array('_articleQueueId' => array_keys($articlesQueue))
                    );
                    if (!empty($articleRecords)) {
                        $articleRecords = BaseFactoryPrepare::Collapse($articleRecords, 'articleQueueId', false);
                    }
                }
            }

            Response::setArray( 'articleRecords', $articleRecords );
            Response::setArray( 'grid', $grid );
        }
    }
?>