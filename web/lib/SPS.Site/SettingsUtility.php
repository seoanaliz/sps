<?php
    /**
     * SettingsUtility
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SettingsUtility {

        private function set($key, $value) {
            Session::setParameter($key, $value);
            Cookie::setCookie($key, $value, time() + 604800, '/', null, false, false);
        }
        private function get($key) {
            $result = Session::getParameter($key);
            if (empty($result)) {
                $result = Cookie::getParameter($key);
            }
            return $result;
        }

        public static function GetTarget() {
            $targetFeedId = self::get('currentTargetFeedId');
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                $targetFeedId = null;
            }

            return $targetFeedId;
        }

        public static function SetTarget($targetFeedId) {
            self::set('currentTargetFeedId', $targetFeedId);
        }

        public static function GetDate() {
            $timestamp = self::get('currentTimestamp');
            if (empty($timestamp)) {
                $currentDate = DateTimeWrapper::Now();
            } else {
                $currentDate = new DateTimeWrapper(date('d.m.Y', $timestamp));
            }

            return $currentDate;
        }

        public static function SetDate($timestamp) {
            self::set('currentTimestamp', $timestamp);
        }

        public static function SetSources($sourceFeedIds, $from, $to) {
            $targetFeedId = self::GetTarget();
            if (!empty($targetFeedId)) {
                self::set('sourceFeedIds' . $targetFeedId, implode(',', $sourceFeedIds));
                self::set('sourceFeedRange' . $targetFeedId, $from . ':' . $to);
            }
        }
    }
?>