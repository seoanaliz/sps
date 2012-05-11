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

            $sourceFeeds = SourceFeedFactory::Get(
                array('_sourceFeedId' => AccessUtility::GetSourceFeedIds($currentTargetFeedId))
                , array( BaseFactory::WithoutPages => true )
            );
            Response::setArray( "sourceFeeds", $sourceFeeds );

            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => AccessUtility::GetTargetFeedIds())
                , array( BaseFactory::WithoutPages => true )
            );
            Response::setArray( "targetFeeds", $targetFeeds );

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