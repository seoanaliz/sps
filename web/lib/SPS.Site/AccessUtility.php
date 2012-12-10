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

            self::$targetFeedIds = array(-1 => -1);
            $userId = AuthVkontakte::IsAuth();

            if (empty($userId)) {
                return self::$targetFeedIds;
            }

            /** @var $editor Editor */
            $editor = Session::getObject('Editor');
            if (!empty($editor) && !empty($editor->targetFeedIds)) {
                foreach ($editor->targetFeedIds as $targetFeedId) {
                    self::$targetFeedIds[$targetFeedId] = $targetFeedId;
                }
            }

            return self::$targetFeedIds;
        }

        /**
         * Возвращает список источников для ленты
         * @param int $currentTargetFeedId
         * @return array
         */
        public static function GetSourceFeedIds($currentTargetFeedId = 0) {
            // wtf???
            $result = array(-1 => -1, -2 => -2);

            if (is_array(self::$sourceFeedIds) && array_key_exists($currentTargetFeedId, self::$sourceFeedIds)) {
                return self::$sourceFeedIds[$currentTargetFeedId];
            }

            $vkId = AuthVkontakte::IsAuth();


            if (empty($vkId)) {
                self::$sourceFeedIds[$currentTargetFeedId] = $result;
                return $result;
            }

            // получеам все строки!!! почему не использовать массив?
            $checkData = SourceFeedFactory::Get(array('containsFeedId' => $currentTargetFeedId),
                array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"sourceFeedId", "targetFeedIds"')
            );

            if (!empty($checkData)) {
                foreach ($checkData as $checkDataItem) {
                    $targetFeedIds = explode(',', $checkDataItem->targetFeedIds);
                    if (!empty($targetFeedIds)) {
                        foreach ($targetFeedIds as $targetFeedId) {
                            if (self::HasAccessToTargetFeedId($targetFeedId)) {
                                if (empty($currentTargetFeedId)) {
                                    $result[$checkDataItem->sourceFeedId] = $checkDataItem->sourceFeedId;
                                    break;
                                } else if($targetFeedId == $currentTargetFeedId) {
                                    $result[$checkDataItem->sourceFeedId] = $checkDataItem->sourceFeedId;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            self::$sourceFeedIds[$currentTargetFeedId] = $result;
            return $result;
        }

        public static function HasAccessToTargetFeedId($targetFeedId, $__editorMode = true) {
            if ($__editorMode) {
                $accessIds = self::GetTargetFeedIds();
                return empty($accessIds) || array_key_exists($targetFeedId, $accessIds);
            } else {
                $accessIds = Session::getArray('targetFeedIds');
                $accessIds = !empty($accessIds) ? $accessIds : array();
                return in_array($targetFeedId, $accessIds);
            }
        }

        public static function HasAccessToSourceFeedId($sourceFeedId) {
            $accessIds = self::GetSourceFeedIds();
            return empty($accessIds) || array_key_exists($sourceFeedId, $accessIds);
        }
    }
?>