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

        $sourceFeed = SourceFeedFactory::GetById($Article->sourceFeedId);
        $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $Article->articleId));

        if (!empty($Article->authorId)) {
            $author = AuthorFactory::GetById($Article->authorId);
            Response::setParameter('author', $author);
        }

        $articleLinkPrefix = 'http://vk.com/wall-';
        if ($sourceFeed->type == SourceFeedUtility::Albums) {
            $articleLinkPrefix = 'http://vk.com/photo';
        }

        Response::setParameter('article', $Article);
        Response::setParameter('articleRecord', $articleRecord);
        Response::setParameter('sourceFeed', $sourceFeed);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo(array($sourceFeed)));
        Response::setArray('commentsData', CommentUtility::GetLastComments(array($Article->articleId)));
        Response::setBoolean('canEditPosts', true);
        Response::setString('articleLinkPrefix', $articleLinkPrefix);
    }
}

?>