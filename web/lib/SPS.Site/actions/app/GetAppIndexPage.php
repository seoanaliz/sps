<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetAppIndexPage Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetAppIndexPage {

        /**
         * Entry Point
         */
        public function Execute() {
            //паблики, к которым у пользователя есть доступ
            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => Session::getArray('targetFeedIds'))
            );
            Response::setArray( 'targetFeeds', $targetFeeds );
            Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId') );
        }
    }
?>