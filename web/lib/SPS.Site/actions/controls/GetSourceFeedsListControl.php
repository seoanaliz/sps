<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetSourceFeedsListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetSourceFeedsListControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $targetFeedId = Request::getInteger( 'targetFeedId' );

            $sourceFeeds = SourceFeedFactory::Get(
                array('_sourceFeedId' => AccessUtility::GetSourceFeedIds($targetFeedId))
                , array( BaseFactory::WithoutPages => true )
            );

            echo ObjectHelper::ToJSON(array_values($sourceFeeds));
        }
    }

?>