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
            $currentSourceFeedId    = Session::getInteger('currentSourceFeedId');
            $currentTargetFeedId    = Session::getInteger('currentTargetFeedId');

            if (!AccessUtility::HasAccessToSourceFeedId($currentSourceFeedId)) {
                $currentSourceFeedId = null;
            }
            if (!AccessUtility::HasAccessToTargetFeedId($currentTargetFeedId)) {
                $currentTargetFeedId = null;
            }

            Response::setInteger('currentSourceFeedId', $currentSourceFeedId);
            Response::setInteger('currentTargetFeedId', $currentTargetFeedId);

            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => AccessUtility::GetTargetFeedIds())
                , array( BaseFactory::WithoutPages => true )
            );
            Response::setArray( "targetFeeds", $targetFeeds );

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

            $currentTimestamp = Session::getInteger('currentTimestamp');
            if (empty($currentTimestamp)) {
                $currentDate = DateTimeWrapper::Now();
            } else {
                $currentDate = new DateTimeWrapper(date('d.m.Y', $currentTimestamp));
            }
            Response::setParameter('currentDate', $currentDate);
        }
    }
?>