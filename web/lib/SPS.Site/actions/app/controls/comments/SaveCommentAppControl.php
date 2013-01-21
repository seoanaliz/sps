<?php
/**
 * SaveCommentAppControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class SaveCommentAppControl extends BaseControl {

    /**
     * Entry Point
     */
    public function Execute()
    {
        $result = array(
            'success' => false
        );

        /** @var $editor Editor */
        $author = $this->getAuthor();
        $editor = Session::getObject('Editor');

        $article = ArticleFactory::GetById(Request::getInteger('id'), array(), array(BaseFactory::WithoutDisabled => false));

        if (empty($article) || $article->sourceFeedId != SourceFeedUtility::FakeSourceAuthors) {
            $result['message'] = 'accessError';
            //echo ObjectHelper::ToJSON($result);
            return false;
        }

        $__editorMode = false;
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        if (!$TargetFeedAccessUtility->canSaveArticleComment($article->targetFeedId)) {
            $result['message'] = 'accessError';
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
        Response::setParameter('comment', $comment);
    }
}

?>