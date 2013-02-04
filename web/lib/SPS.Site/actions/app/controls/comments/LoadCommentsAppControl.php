<?php
/**
 * LoadCommentsAppControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class LoadCommentsAppControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $article = ArticleFactory::GetById(Request::getInteger('postId'), array(), array(BaseFactory::WithoutDisabled => false));

        if (empty($article)) {
            return false;
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        if (!$TargetFeedAccessUtility->canShowArticleComments($article->targetFeedId)) {
            return false;
        }

        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($article->targetFeedId);

        $all = Request::getBoolean('all');
        $authorEvents = AuthorEventFactory::Get(array('articleId' => $article->articleId));
        $commentsData = CommentUtility::GetLastComments(array($article->articleId), !$all, $authorEvents);

        Response::setParameter('article', $article);
        Response::setArray('commentsData', $commentsData);
        Response::setInteger('authorId', $this->getAuthor()->authorId);
        Response::setBoolean('isWebUserEditor',  $role && $role != UserFeed::ROLE_AUTHOR);
        Response::setArray('authorEvents', $authorEvents);

        if (!empty($all)) {
            Response::setBoolean('showHideBtn', true);
        }
    }
}

?>