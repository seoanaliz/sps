<?php
    Package::Load( 'SPS.Site' );

    /**
     * LoadCommentsAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class LoadCommentsAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $article = ArticleFactory::GetById(Request::getInteger('postId'));

            if (empty($article)) {
                return false;
            }

            if (!in_array($article->targetFeedId, Session::getArray('targetFeedIds'))) {
                return false;
            }

            $all = Request::getBoolean( 'all' );
            $commentsData = CommentUtility::GetLastComments(array($article->articleId), !$all);

            Response::setParameter( 'article', $article );
            Response::setArray( 'commentsData', $commentsData );

            if (!empty($all)) {
                Response::setBoolean( 'showHideBtn', true );
            }
        }
    }
?>