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
         * @param $date
         * @param $targetFeed
         * @return array
         *
         * @deprecated see GridLineUtility::GetGrid
         */
        private function getGrid($date, $targetFeed) {
            //generate table
            $result = array();

            $startTime  = '9:00';
            $period     = 60;

            if (!empty($targetFeed)) {
                //ищем настройки сетки
                $queueDate = new DateTimeWrapper($date);
                $sqlDate = PgSqlConvert::ToDate($queueDate);
                $customSql = " AND cast(\"startDate\" as DATE) <= {$sqlDate} ORDER BY \"startDate\" DESC LIMIT 1 ";
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
            $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);

            $type = Request::getString('type');
            if (empty($type) || empty(GridLineUtility::$Types[$type])) {
                $type = GridLineUtility::TYPE_ALL;
            }

            $queueDate  = new DateTimeWrapper($date);
            $today      = new DateTimeWrapper(date('d.m.Y'));
            $isHistory  = ($queueDate < $today);

            Session::setInteger('currentTimestamp', $queueDate->format('U'));

            $targetFeedId = Request::getInteger( 'targetFeedId' );
            $targetFeed   = TargetFeedFactory::GetById($targetFeedId);

            $articleRecords = array();
            $articlesQueue  = array();

            //check access
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                $targetFeedId = null;
            }

            if(!empty($targetFeedId) && !empty($targetFeed)) {
                Session::setInteger('currentTargetFeedId', $targetFeedId);

                //вытаскиваем всю очередь на этот день на этот паблик
                $articlesQueue = ArticleQueueFactory::Get(
                    array(
                        'targetFeedId' => $targetFeedId
                        , 'startDateAsDate' => $date
                        , 'type' => ($type == GridLineUtility::TYPE_ALL) ? null : $type
                    )
                    , array(
                        BaseFactory::WithoutPages => true
                        , BaseFactory::OrderBy => ' "sentAt" DESC '
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
                    foreach ($grid as &$gridItem) {
                        if ($gridItem['dateTime'] >= $articlesQueueItem->startDate && $gridItem['dateTime'] <= $articlesQueueItem->endDate) {
                            if (empty($gridItem['queue'])) {
                                $gridItem['queue'] = $articlesQueueItem;
                                $gridItem['blocked'] = ($articlesQueueItem->statusId != 1 || $articlesQueueItem->endDate <= $now);
                                $gridItem['failed'] = ($articlesQueueItem->statusId == 1 && $articlesQueueItem->endDate <= $now);
                            }
                        }
                    }
                }
            }

            Response::setArray( 'grid', $grid );
        }
    }
?>