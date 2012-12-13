<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetIndexPage Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetIndexPage {

        /**
         * Entry Point
         */
        public function Execute() {

            $userId = AuthVkontakte::IsAuth();

            /**
             * current values from settings
             */
            $currentTargetFeedId = SettingsUtility::GetTarget();

            /**
             * target feeds
             */
            $targetFeeds = TargetFeedFactory::getUserTargetFeeds($userId);

            if (empty($currentTargetFeedId)) {
                //пытаемся получить источники для первого паблика
                if (!empty($targetFeeds)) {
                    $currentTargetFeedId = current(array_keys($targetFeeds));
                } else {
                    $currentTargetFeedId = -1;
                }
            }

            $RoleUtility = new RoleAccessUtility();
            $sourceTypes = $gridTypes = array();
            if ($currentTargetFeedId) {
                $sourceTypes = $RoleUtility->getAccessibleSourceTypes($currentTargetFeedId);
                $gridTypes = $RoleUtility->getAccessibleGridTypes($currentTargetFeedId);
            }

            $sourceFeeds = SourceFeedFactory::Get(
                array('_sourceFeedId' => AccessUtility::GetSourceFeedIds($currentTargetFeedId))
                , array( BaseFactory::WithoutPages => true )
            );

            Response::setArray('sourceFeeds', $sourceFeeds );
            Response::setArray('targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId') );
            Response::setArray('targetFeeds', $targetFeeds );
            Response::setInteger('currentTargetFeedId', $currentTargetFeedId);
            Response::setParameter('currentDate', SettingsUtility::GetDate());
            Response::setParameter('RoleUtility', $RoleUtility);
            Response::setParameter('sourceTypes', $sourceTypes);
            Response::setParameter('gridTypes', $gridTypes);
        }
    }
?>