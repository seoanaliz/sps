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
            $article = ArticleFactory::GetById(Request::getInteger('postId'), array(), array(BaseFactory::WithoutDisabled => false));

            if (empty($article)) {
                return false;
            }

            $__editorMode = Response::getBoolean('__editorMode');
            if (!AccessUtility::HasAccessToTargetFeedId($article->targetFeedId, $__editorMode)) {
                $result['message'] = 'accessError';
                //echo ObjectHelper::ToJSON($result);
                return false;
            }

            $all = Request::getBoolean( 'all' );
            $authorEvents = AuthorEventFactory::Get(array('articleId' => $article->articleId));
            $commentsData = CommentUtility::GetLastComments(array($article->articleId), !$all, $authorEvents);

            Response::setParameter( 'article', $article );
            Response::setArray( 'commentsData', $commentsData );
            Response::setArray( 'authorEvents', $authorEvents );

            if (!empty($all)) {
                Response::setBoolean( 'showHideBtn', true );
            }
        }
    }
?>