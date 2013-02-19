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
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Empty article id'));
            return;
        }

        $Article = ArticleFactory::GetById($id);
        if (empty($Article)) {
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Cant load article'));
            return;
        }

        $SourceAccessUtility = new SourceAccessUtility($this->vkId);

        //check access
        if (!$SourceAccessUtility->hasAccessToSourceFeed($Article->sourceFeedId)) {
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Access denied'));
            return;
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        $role = null;

        // лента есть не у всех постов. У спарсеных нет. Проверяем при необходимости.
        if ($Article->targetFeedId){
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($Article->targetFeedId);
        } else {
            $SourceFeed = SourceFeedFactory::GetById($Article->sourceFeedId);
            if ($SourceFeed) {
                $roles = array();
                foreach (explode(',', $SourceFeed->targetFeedIds) as $targetFeedId){
                    $roles = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
                }
                if ($roles) {
                    $role = max($roles);
                }
            }
        }

        if (is_null($role)){
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Empty role for ' . $this->vkId . ' target feed' . $targetFeedId));
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
        Response::setInteger('isWebUserEditor', $role != UserFeed::ROLE_AUTHOR);
        Response::setString('articleLinkPrefix', $articleLinkPrefix);
    }
}

?>