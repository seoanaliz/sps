<?php
/**
 * DeleteCommentAppControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class DeleteCommentAppControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $id = Request::getInteger('id');

        if (empty($id)) {
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Empty comment id'));
            return;
        }

        $comment = CommentFactory::GetById($id);
        if (empty($comment)) {
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Cant find comment'));
            return;
        }

        $article = ArticleFactory::GetById($comment->articleId, array(), array(BaseFactory::WithoutDisabled => false));

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($article->targetFeedId);

        if ($role != UserFeed::ROLE_AUTHOR) {
            // ок, редактор может удалять комменты
        } elseif ($role == UserFeed::ROLE_AUTHOR) {
            // ок, автор может удалять свои комменты
            /** @var $author Author */
            $author = $this->getAuthor();
            if ($author->authorId != $comment->authorId) {
                echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Author not equal'));
                return;
            }
        }

        $comment->statusId = 3;
        CommentFactory::UpdateByMask($comment, array('statusId'), array('commentId' => $comment->commentId));

        AuthorEventUtility::EventCommentRemove($article, $comment->commentId);
    }
}

?>