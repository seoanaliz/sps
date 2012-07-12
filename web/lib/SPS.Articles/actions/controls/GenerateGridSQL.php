<?php
    Package::Load( 'SPS.Articles' );

    /**
     * GenerateGridSQL Action
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class GenerateGridSQL {

        /**
         * Entry Point
         */
        public function Execute() {
            Logger::LogLevel( ELOG_DEBUG );

            $targetFeeds = TargetFeedFactory::Get();
            $date = date('d.m.Y');

            ConnectionFactory::BeginTransaction();

            foreach ($targetFeeds as $targetFeed) {
                $grid = $this->getGrid($date, $targetFeed);

                foreach ($grid as $gridItem) {
                    $o = new GridLine();
                    $o->startDate = $gridItem['dateTime'];
                    $o->endDate = new DateTimeWrapper($gridItem['dateTime']);
                    $o->endDate->modify('+1 month');
                    $o->time = new DateTimeWrapper($gridItem['dateTime']);;
                    $o->targetFeedId = $targetFeed->targetFeedId;
                    $o->type = GridLineUtility::TYPE_CONTENT;
                    GridLineFactory::Add($o);
                }
            }

            ConnectionFactory::CommitTransaction(false);
        }

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
    }
?>