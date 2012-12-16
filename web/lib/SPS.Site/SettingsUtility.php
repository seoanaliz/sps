<?php
    /**
     * SettingsUtility
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SettingsUtility {

        public static function GetTarget() {
            $targetFeedId = Cookie::getParameter('currentTargetFeedId');
            return $targetFeedId;
        }

        public static function GetDate() {
            return DateTimeWrapper::Now();
        }
    }
?>