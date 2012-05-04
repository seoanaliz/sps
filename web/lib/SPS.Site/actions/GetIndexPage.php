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
            $sourceFeeds = SourceFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "sourceFeeds", $sourceFeeds );

            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "targetFeeds", $targetFeeds );

            $currentSourceFeedId = Session::getInteger('currentSourceFeedId');
            $currentTargetFeedId = Session::getInteger('currentTargetFeedId');
            Response::setInteger('currentSourceFeedId', $currentSourceFeedId);
            Response::setInteger('currentTargetFeedId', $currentTargetFeedId);
        }
    }
?>