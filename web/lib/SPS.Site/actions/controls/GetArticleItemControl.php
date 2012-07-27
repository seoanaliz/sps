<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticleItemControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticleItemControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $object = ArticleFactory::GetById($id);
            if (empty($object)) {
                return;
            }

            //check access
            if (!AccessUtility::HasAccessToSourceFeedId($object->sourceFeedId)) {
                return;
            }

            $sourceFeed = SourceFeedFactory::GetById($object->sourceFeedId);
            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $object->articleId));

            if (!empty($object->authorId)) {
                $author = AuthorFactory::GetById($object->authorId);
                Response::setParameter( 'author', $author );
            }

            Response::setParameter( 'article', $object );
            Response::setParameter( 'articleRecord', $articleRecord );
            Response::setParameter( 'sourceFeed', $sourceFeed );
            Response::setArray( 'sourceInfo', SourceFeedUtility::GetInfo(array($sourceFeed)) );
        }
    }

?>