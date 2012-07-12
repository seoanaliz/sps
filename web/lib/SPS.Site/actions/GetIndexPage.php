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

            /**
             * current values from settings
             */
            $currentTargetFeedId = SettingsUtility::GetTarget();

            /**
             * target feeds
             */
            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => AccessUtility::GetTargetFeedIds())
                , array( BaseFactory::WithoutPages => true )
            );

            if (empty($currentTargetFeedId)) {
                //пытаемся получить источники для первого паблика
                if (!empty($targetFeeds)) {
                    $currentTargetFeedId = current(array_keys($targetFeeds));
                } else {
                    $currentTargetFeedId = -1;
                }
            }

            $sourceFeeds = SourceFeedFactory::Get(
                array('_sourceFeedId' => AccessUtility::GetSourceFeedIds($currentTargetFeedId))
                , array( BaseFactory::WithoutPages => true )
            );

            Response::setArray( "sourceFeeds", $sourceFeeds );
            Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId') );
            Response::setArray( "targetFeeds", $targetFeeds );
            Response::setInteger('currentTargetFeedId', $currentTargetFeedId);
            Response::setParameter('currentDate', SettingsUtility::GetDate());
        }
    }
?>