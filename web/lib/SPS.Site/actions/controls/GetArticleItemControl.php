<?php
/**
 * GetArticleItemControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticleItemControl extends BaseControl
{
    /**
     * Entry Point
     */
    public function Execute()
    {
        $id = Request::getInteger('id');

        if (empty($id)) {
            return;
        }

        $Article = ArticleFactory::GetById($id);
        if (empty($Article)) {
            return;
        }

        $SourceAccessUtility = new SourceAccessUtility($this->vkId);

        //check access
        if (!$SourceAccessUtility->hasAccessToSourceFeed($Article->sourceFeedId)) {
            return;
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($Article->targetFeedId);
        if (is_null($role)){
            return;
        }

        $canEditPost = true;
        if ($role == UserFeed::ROLE_AUTHOR) {
            $canEditPost = $Article->articleStatus != Article::STATUS_APPROVED;
        }

        $sourceFeed = SourceFeedFactory::GetById($Article->sourceFeedId);
        $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $Article->articleId));

        if (!empty($Article->authorId)) {
            $author = AuthorFactory::GetById($Article->authorId);
            Response::setParameter('author', $author);
        }

        $articleLinkPrefix = 'http://vk.com/wall-';
        if ($sourceFeed && $sourceFeed->type == SourceFeedUtility::Albums) {
            $articleLinkPrefix = 'http://vk.com/photo';
        }

        Response::setParameter('article', $Article);
        Response::setParameter('articleRecord', $articleRecord);
        Response::setParameter('sourceFeed', $sourceFeed);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo(array($sourceFeed)));
        Response::setArray('commentsData', CommentUtility::GetLastComments(array($Article->articleId)));
        Response::setBoolean('canEditPost', $canEditPost);
        Response::setInteger('authorId', $this->getAuthor()->authorId);
        Response::setString('articleLinkPrefix', $articleLinkPrefix);
    }
}

?>