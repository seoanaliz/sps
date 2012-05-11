<?php
    /**
     * AccessUtility
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class AccessUtility {

        private static $targetFeedIds = null;
        private static $sourceFeedIds = null;

        public static function GetTargetFeedIds() {
            if (!is_null(self::$targetFeedIds)) {
                return self::$targetFeedIds;
            }

            $result = array(-1 => -1);
            $userId = AuthVkontakte::IsAuth();

            if (empty($userId)) {
                self::$targetFeedIds = $result;
                return $result;
            }

            //TODO super admin check
//            $result = array();
//            self::$targetFeedIds = $result;
//            return $result;

            $checkData = TargetFeedFactory::Get(
                array()
                , array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"targetFeedId", "vkIds"')
            );

            if (!empty($checkData)) {
                foreach ($checkData as $checkDataItem) {
                    $vkIds = explode(',', $checkDataItem->vkIds);
                    if (in_array($userId, $vkIds)) {
                        $result[$checkDataItem->targetFeedId] = $checkDataItem->targetFeedId;
                    }
                }
            }

            self::$targetFeedIds = $result;
            return $result;
        }

        public static function GetSourceFeedIds() {
            if (!is_null(self::$sourceFeedIds)) {
                return self::$sourceFeedIds;
            }

            $result = array(-1 => -1);
            $userId = AuthVkontakte::IsAuth();

            if (empty($userId)) {
                self::$sourceFeedIds = $result;
                return $result;
            }

            //TODO super admin check
            //            $result = array();
            //            self::$sourceFeedIds = $result;
            //            return $result;

            $checkData = SourceFeedFactory::Get(
                array()
                , array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"sourceFeedId", "targetFeedIds"')
            );

            if (!empty($checkData)) {
                foreach ($checkData as $checkDataItem) {
                    $targetFeedIds = explode(',', $checkDataItem->targetFeedIds);
                    if (!empty($targetFeedIds)) {
                        foreach ($targetFeedIds as $targetFeedId) {
                            if (self::HasAccessToTargetFeedId($targetFeedId)) {
                                $result[$checkDataItem->sourceFeedId] = $checkDataItem->sourceFeedId;
                                break;
                            }
                        }
                    }
                }
            }

            self::$sourceFeedIds = $result;
            return $result;
        }

        public static function HasAccessToTargetFeedId($targetFeedId) {
            $accessIds = self::GetTargetFeedIds();
            return array_key_exists($targetFeedId, $accessIds);
        }

        public static function HasAccessToSourceFeedId($sourceFeedId) {
            $accessIds = self::GetTargetFeedIds();
            return array_key_exists($sourceFeedId, $accessIds);
        }
    }
?>