<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteCommentAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteCommentAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $comment = CommentFactory::GetById($id);
            if (empty($comment)) {
                return;
            }

            $__editorMode = Response::getBoolean('__editorMode');
            $article = ArticleFactory::GetById($comment->articleId, array(), array(BaseFactory::WithoutDisabled => false));
            if ($__editorMode) {
                if (!AccessUtility::HasAccessToTargetFeedId($article->targetFeedId)) {
                    return;
                }
            } else {
                /** @var $author Author */
                $author = Session::getObject('Author');
                if ($comment->authorId != $author->authorId) {
                    return;
                }
            }

            $comment->statusId = 3;
            CommentFactory::UpdateByMask($comment, array('statusId'), array('commentId' => $comment->commentId));

            AuthorEventUtility::EventCommentRemove($article, $comment->commentId);
        }
    }
?>