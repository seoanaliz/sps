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

            $targetFeedId = Request::getInteger( 'targetFeedId' );

            $grid = $this->getGrid($date);

            Response::setArray( 'grid', $grid );
        }
    }
?>