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
            return Cookie::getParameter($key);
        }

        public static function GetTarget($checkAccess = true) {
            $targetFeedId = self::get('currentTargetFeedId');
            if ($checkAccess && !AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                $targetFeedId = null;
            }

            return $targetFeedId;
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
            $targetFeedId = self::GetTarget(false);
            if (!empty($targetFeedId)) {
                self::set('sourceFeedIds' . $targetFeedId, implode(',', $sourceFeedIds));
            }
        }
    }
?>