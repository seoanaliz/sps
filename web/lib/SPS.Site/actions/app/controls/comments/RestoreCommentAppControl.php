<?php
    Package::Load( 'SPS.Site' );

    /**
     * RestoreCommentAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class RestoreCommentAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $__editorMode = Response::getBoolean('__editorMode');
            if ($__editorMode) {
                CommentFactory::$mapping['view'] = CommentFactory::$mapping['table'];
                $comment = CommentFactory::GetById($id, array(), array(BaseFactory::WithoutPages => false));
                if (empty($comment)) {
                    return;
                }
                $article = ArticleFactory::GetById($comment->articleId);
                if (!AccessUtility::HasAccessToTargetFeedId($article->targetFeedId)) {
                    return;
                }

                $comment->statusId = 1;
                CommentFactory::UpdateByMask($comment, array('statusId'), array('commentId' => $comment->commentId));
            } else {
                /** @var $author Author */
                $author = Session::getObject('Author');

                $o = new Comment();
                $o->statusId = 1;
                CommentFactory::UpdateByMask($o, array('statusId'), array('commentId' => $id, 'authorId' => $author->authorId, 'statusId' => 3));
            }
        }
    }
?>