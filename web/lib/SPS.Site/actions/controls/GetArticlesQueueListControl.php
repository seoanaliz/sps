<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticlesQueueListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticlesQueueListControl {

        private function getGrid($date) {
            //generate table
            $result = array();
            $now = DateTimeWrapper::Now();
            for ($i = 24; $i >= 9; $i--) {
                $queueDate = new DateTimeWrapper($date);
                $queueDate->modify('+ ' . $i . 'hours');
                if ($i == 24) {
                    $queueDate->modify('-1 minute');
                }

                $result[] = array(
                    'dateTime' => $queueDate,
                    'blocked' => ($queueDate <= $now)
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

            $queueDate  = new DateTimeWrapper($date);
            $today      = new DateTimeWrapper(date('d.m.Y'));
            $isHistory  = ($queueDate < $today);

            $grid = $this->getGrid($date);

            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $targetFeed   = TargetFeedFactory::GetById($targetFeedId);

            $articleRecords = array();
            $articlesQueue  = array();

            if(!empty($targetFeedId) && !empty($targetFeed)) {
                //вытаскиваем всю очередь на этот день на этот паблик
                $articlesQueue = ArticleQueueFactory::Get(
                    array('targetFeedId' => $targetFeedId, 'startDateAsDate' => $date)
                    , array(
                        BaseFactory::WithoutPages => true
                        , BaseFactory::OrderBy => ' "sentAt" DESC '
                    )
                );

                if(!empty($articlesQueue)) {
                    foreach($articlesQueue as $articlesQueueItem) {
                        //ищем место в grid для текущей $articlesQueueItem
                        foreach ($grid as &$gridItem) {
                            if ($gridItem['dateTime'] >= $articlesQueueItem->startDate && $gridItem['dateTime'] <= $articlesQueueItem->endDate) {
                                if (empty($gridItem['queue'])) {
                                    $gridItem['queue'] = $articlesQueueItem;
                                    $gridItem['blocked'] = ($articlesQueueItem->statusId != 1);
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

            Response::setArray( 'articleRecords',   $articleRecords );
            Response::setArray( 'articlesQueue',    $articlesQueue );
            Response::setArray( 'grid',             $grid );

            if ($isHistory) {
                Page::$TemplatePath = 'tmpl://fe/elements/arcticles-queue-history.tmpl.php';
            }
        }
    }
?>