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
            $targetFeedId = Request::getInteger('targetFeedId');

            $type = Request::getString( 'type' );
            if (empty($type) || empty(SourceFeedUtility::$Types[$type])) {
                $type = SourceFeedUtility::Source;
            }

            $result = array();
            if (!empty($targetFeedId)) {
                if ($type == SourceFeedUtility::Authors) {
                    $authors = AuthorFactory::Get(
                        array(),
                        array(
                            BaseFactory::WithoutPages => true,
                            BaseFactory::CustomSql => ' AND "targetFeedIds" @> ARRAY[' . PgSqlConvert::ToInt($targetFeedId) . '] '
                        )
                    );

                    foreach ($authors as $author) {
                        $result[] =  array(
                            'id' => $author->authorId,
                            'title' => $author->FullName()
                        );
                    }
                } else {
                    $sourceFeeds = SourceFeedFactory::Get(
                        array('_sourceFeedId' => AccessUtility::GetSourceFeedIds($targetFeedId), 'type' => $type)
                        , array( BaseFactory::WithoutPages => true )
                    );

                    foreach ($sourceFeeds as $sourceFeed) {
                        $result[] =  array(
                            'id' => $sourceFeed->sourceFeedId,
                            'title' => $sourceFeed->title
                        );
                    }
                }
            }

            $RoleUtility = new RoleUtility();






            echo ObjectHelper::ToJSON(array(
                'sourceFeeds' => $result,
                'accessibleSourceTypes' => array_keys($RoleUtility->getAccessibleSourceTypes($targetFeedId)),
                'accessibleGridTypes' => array_keys($RoleUtility->getAccessibleGridTypes($targetFeedId)),
                'canAddPlanCell' => $RoleUtility->canAddPlanCell($targetFeedId)
            ));
        }
    }

?>