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
             * current values from session
             */
            $currentTargetFeedId    = Session::getInteger('currentTargetFeedId');
            if (!AccessUtility::HasAccessToTargetFeedId($currentTargetFeedId)) {
                $currentTargetFeedId = null;
            }

            $currentSourceType = Session::getString('currentSourceType');
            if (empty($currentSourceType) || empty(SourceFeedUtility::$Types[$currentSourceType])) {
                $currentSourceType = SourceFeedUtility::Source;
            }
            Response::setString('currentSourceType', $currentSourceType);

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

            $currentTimestamp = Session::getInteger('currentTimestamp');
            if (empty($currentTimestamp)) {
                $currentDate = DateTimeWrapper::Now();
            } else {
                $currentDate = new DateTimeWrapper(date('d.m.Y', $currentTimestamp));
            }

            Response::setArray( "sourceFeeds", $sourceFeeds );
            Response::setArray('currentSourceFeedIds', Session::getArray('currentSourceFeedIds'));
            Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId') );
            Response::setArray( "targetFeeds", $targetFeeds );
            Response::setInteger('currentTargetFeedId', $currentTargetFeedId);
            Response::setParameter('currentDate', $currentDate);
        }
    }
?>