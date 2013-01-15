<?php
    /**
     * GetArticleItemControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticleItemControl extends BaseControl {

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

            $SourceAccessUtility = new SourceAccessUtility($this->vkId);

            //check access
            if (!$SourceAccessUtility->hasAccessToSourceFeed($object->sourceFeedId)) {
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
            Response::setArray( 'commentsData', CommentUtility::GetLastComments(array($object->articleId)));
        }
    }

?>