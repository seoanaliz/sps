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
            /** @var $author Author */
            $author = Session::getObject('Author');

            $article = ArticleFactory::GetById(Request::getInteger('id'));

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