<?php
    /**
     * RestoreCommentAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class RestoreCommentAppControl extends AppBaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $__editorMode = false; //Response::getBoolean('__editorMode');
            if ($__editorMode) {
                /*CommentFactory::$mapping['view'] = CommentFactory::$mapping['table'];
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
                AuthorEventUtility::EventComment($article, $comment->commentId);      */
            } else {
                /** @var $author Author */
                $author = $this->getAuthor();

                $o = new Comment();
                $o->statusId = 1;
                CommentFactory::UpdateByMask($o, array('statusId'), array('commentId' => $id, 'authorId' => $author->authorId, 'statusId' => 3));
            }
        }
    }
?>