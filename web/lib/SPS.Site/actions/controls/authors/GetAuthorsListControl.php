<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetAuthorsListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     shuler
     */
    class GetAuthorsListControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                return;
            }

            if (!empty($targetFeedId)) {
                $authors = AuthorFactory::Get(
                    array()
                    , array(
                        BaseFactory::WithoutPages => true
                    , BaseFactory::OrderBy => ' "firstName", "lastName" '
                    , BaseFactory::CustomSql => ' AND"targetFeedIds" @> \'{' . PgSqlConvert::ToInt($targetFeedId) . '}\''
                    )
                );
            } else {
                $authors = array();
            }

            Response::setArray( 'authors', $authors );
        }
    }

?>