<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticlesQueueListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticlesQueueListControl {

        private function getGrid($date, $targetFeed) {
            //generate table
            $result = array();

            $startTime  = '9:00';
            $period     = 60;

            if (!empty($targetFeed)) {
                //ищем настройки сетки
                $queueDate = new DateTimeWrapper($date);
                $customSql = " AND cast(\"startDate\" as DATE) <= '{$queueDate->DefaultDateFormat()}' ORDER BY \"startDate\" DESC LIMIT 1 ";
                $targetFeedGrid = TargetFeedGridFactory::GetOne(
                    array('targetFeedId' => $targetFeed->targetFeedId)
                    , array(BaseFactory::CustomSql => $customSql)
                );

                if (!empty($targetFeedGrid)) {
                    $period = $targetFeedGrid->period;
                    $startTime = $targetFeedGrid->startDate->format('G:i');
                }
            }

            $period = max($period, 15);

            //строим сетку
            $now = DateTimeWrapper::Now();
            $queueDate = new DateTimeWrapper($date . ' ' . $startTime);
            while ($queueDate->DefaultDateFormat() == $date) {
                $result[] = array(
                    'dateTime' => new DateTimeWrapper($queueDate->DefaultFormat()),
                    'blocked' => ($queueDate <= $now)
                );

                $queueDate->modify('+ ' . $period . ' minutes');

                //фикс полуночи
                if ($queueDate->format('G:i') == '0:00') {
                    $queueDate->modify('-1 minute');
                }
            }

            $result = array_reverse($result);

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

            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $targetFeed   = TargetFeedFactory::GetById($targetFeedId);

            $grid = $this->getGrid($date, $targetFeed);

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