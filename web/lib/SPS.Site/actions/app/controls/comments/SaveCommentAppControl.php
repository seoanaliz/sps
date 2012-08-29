<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveCommentAppControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveCommentAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            /** @var $author Author */
            /** @var $editor Editor */
            $author = Session::getObject('Author');
            $editor = Session::getObject('Editor');

            $article = ArticleFactory::GetById(Request::getInteger('id'), array(), array(BaseFactory::WithoutDisabled => false));

            if (empty($article) || $article->sourceFeedId != -1) {
                $result['message'] = 'accessError';
                //echo ObjectHelper::ToJSON($result);
                return false;
            }

            $__editorMode = Response::getBoolean('__editorMode');
            if (!AccessUtility::HasAccessToTargetFeedId($article->targetFeedId, $__editorMode)) {
                $result['message'] = 'accessError';
                //echo ObjectHelper::ToJSON($result);
                return false;
            }

            $comment = new Comment();
            $comment->text = Request::getString('text');
            $comment->articleId = $article->articleId;
            $comment->createdAt = DateTimeWrapper::Now();
            $comment->statusId = 1;

            if ($__editorMode) {
                $comment->editorId = $editor->editorId;
            } else {
                $comment->authorId = $author->authorId;
            }

            $errors = CommentFactory::Validate($comment);
            if (!empty($errors)) {
                $result['message'] = 'saveError';
                //echo ObjectHelper::ToJSON($result);
                return false;
            }

            CommentFactory::Add($comment, array(BaseFactory::WithReturningKeys => true));

            if ($__editorMode || $author->authorId != $article->authorId) {
                AuthorEventUtility::EventComment($article, $comment->commentId);
            }

            $comment = CommentFactory::GetById($comment->commentId);
            Response::setParameter( 'comment', $comment );
        }
    }
?>