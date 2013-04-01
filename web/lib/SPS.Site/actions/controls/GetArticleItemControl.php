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
        $targetFeedId = Request::getInteger('targetFeedId');

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

        // определяем роль
        if ($Article->sourceFeedId && !in_array($Article->sourceFeedId, array(SourceFeedUtility::FakeSourceAuthors,
            SourceFeedUtility::FakeSourceTopface))){
            // если у поста есть источник, то проверяем, привязана ли он к ленте и по ленте определяем роль
            $SourceFeed = SourceFeedFactory::GetById($Article->sourceFeedId);
            if ($SourceFeed){
                $sourceFeedTargetFeeds = explode(',', $SourceFeed->targetFeedIds);
                if (in_array($targetFeedId, $sourceFeedTargetFeeds)){
                    $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
                } else {
                    throw new Exception('TargetFeed ' . $targetFeedId . ' not in source feed targets ' . $Article->sourceFeedId);
                }
            } else {
                throw new Exception('Cant find SourceFeed::' . $Article->sourceFeedId);
            }
        } elseif ($Article->targetFeedId and $Article->targetFeedId == $targetFeedId){
            // нет источника - пробуем определить по ленте
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($Article->targetFeedId);
        } else {
            // пока других вариантов нет
        }

        if (is_null($role)){
            echo ObjectHelper::ToJSON(array('result' => false, 'message' => 'Empty role for vk::' . $this->vkId . ' target feed::' . $targetFeedId));
            return;
        }

        $sourceFeed = SourceFeedFactory::GetById($Article->sourceFeedId);
        if( !$sourceFeed ) $sourceFeed = new SourceFeed();
        if (!empty($Article->authorId)) {
            $author = AuthorFactory::GetById($Article->authorId);
            Response::setParameter('author', $author);
        }

        $articleLinkPrefix = 'http://vk.com/wall-';
        if ($sourceFeed && $sourceFeed->type == SourceFeedUtility::Albums) {
            $articleLinkPrefix = 'http://vk.com/photo';
        }

        $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $Article->articleId));
        $repostArticleRecord = null;
        if( $articleRecord->repostArticleRecordId ) {
            $repostArticleRecord = ArticleRecordFactory::GetOne(array('articleRecordId' => $articleRecord->repostArticleRecordId ));
        }

        Response::setParameter('articleRecord', $repostArticleRecord);
        Response::setParameter('article', $Article);
        Response::setParameter('articleRecord', $articleRecord);
        Response::setParameter('sourceFeed', $sourceFeed);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo(array($sourceFeed)));
        Response::setArray('commentsData', CommentUtility::GetLastComments(array($Article->articleId)));
        Response::setInteger('authorId', $this->getAuthor()->authorId);
        Response::setInteger('isWebUserEditor', $role != UserFeed::ROLE_AUTHOR);
        Response::setString('articleLinkPrefix', $articleLinkPrefix);
        Response::setString('sourceFeedType', $sourceFeed->type);
    }
}

?>
