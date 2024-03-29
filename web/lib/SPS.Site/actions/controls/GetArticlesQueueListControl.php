<?php
/**
 * GetArticlesQueueListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticlesQueueListControl extends BaseControl {

    /**
     * Entry Point
     */
    public function Execute() {
        $timestamp = Request::getInteger( 'timestamp' );
        $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);

        $type = Request::getString('type');
        if (empty($type) || empty(GridLineUtility::$Types[$type])) {
            $type = GridLineUtility::TYPE_ALL;
        }

        $queueDate  = new DateTimeWrapper($date);
        $today      = new DateTimeWrapper(date('d.m.Y'));
        $isHistory  = ($queueDate < $today);

        $targetFeedId = Request::getInteger( 'targetFeedId' );
        $targetFeed   = TargetFeedFactory::GetById($targetFeedId);

        $articleRecords = array();
        $repostArticleRecords = array();
        $articlesQueues  = array();
        $authors = array();


        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
        if (is_null($role)){
            return;
        }
        Response::setBoolean('canEditQueue', $role != UserFeed::ROLE_AUTHOR);

        if(!empty($targetFeedId) && !empty($targetFeed)) {
            //вытаскиваем всю очередь на этот день на этот паблик
            $startDate = new DateTimeWrapper($date);
            $startDate->modify('-30 seconds');

            $endDate = new DateTimeWrapper($date);
            $endDate->modify('+1 day -31 seconds');

            $articlesQueues = ArticleQueueFactory::Get(
                array(
                    'targetFeedId' => $targetFeedId,
                    'startDateFrom' => $startDate,
                    'startDateTo' => $endDate,
                    'type' => ($type == GridLineUtility::TYPE_ALL) ? null : $type,
                )
                , array(
                    BaseFactory::WithoutPages => true,
                    BaseFactory::OrderBy => ' "startDate" DESC ',
                )
            );

            if(!empty($articlesQueues)) {
                //load articles data
                $articleIds = array();
                foreach ($articlesQueues as $articlesQueue) {
                    $articleIds[$articlesQueue->articleQueueId] = $articlesQueue->articleId;
                }

                $articles = $authorIds = array();
                if ($articleIds){
                    $authorIds = array();
                    ArticleFactory::$mapping['view'] = 'articles';
                    foreach (ArticleFactory::Get(array('_articleId' => $articleIds), array(BaseFactory::WithoutPages => true, BaseFactory::WithoutDisabled => false)) as $article){
                        $articles[$article->articleId] = $article;
                        $authorIds[] = $article->authorId;
                    }
                }


                if ($articles && $authorIds) {
                    $authors = array();
                    foreach (AuthorFactory::Get(array('_authorId' => $authorIds), array(BaseFactory::WithoutPages => true)) as $author){
                        $authors[$author->authorId] = $author;
                    }

                    foreach ($articlesQueues as $articlesQueue) {

                        if (isset($articles[$articlesQueue->articleId])
                            && isset($authors[$articles[$articlesQueue->articleId]->authorId])) {
                            $articlesQueue->articleAuthor = $authors[$articles[$articlesQueue->articleId]->authorId];
                        }
                    }
                }

                // load art queue editors
                $vkIds = array();
                foreach ($articlesQueues as $articlesQueue) {
                    if ($articlesQueue->author) {
                        $vkIds[] = $articlesQueue->author;
                    }
                }

                if ($vkIds) {
                    $editors = array();
                    foreach (AuthorFactory::Get(array('vkIdIn' => $vkIds), array(BaseFactory::WithoutPages => true)) as $author) {
                        $editors[$author->vkId] = $author;
                    }

                    if ($editors) {
                        foreach ($articlesQueues as $articlesQueue) {
                            if (isset($editors[$articlesQueue->author])) {
                                $articlesQueue->articleQueueCreator = $editors[$articlesQueue->author];
                            }
                        }
                    }
                }




                // load art records
                $articleRecords = ArticleRecordFactory::Get(
                    array('_articleQueueId' => array_keys($articlesQueues))
                );
                if (!empty($articleRecords)) {
                    $articleRecords = BaseFactoryPrepare::Collapse($articleRecords, 'articleQueueId', false);

                    $repostArticleRecordsIds =   array_filter( ArrayHelper::GetObjectsFieldValues($articleRecords, array('repostArticleRecordId')));
                    if (!empty($repostArticleRecordsIds)) {
                        $repostRecords = ArticleRecordFactory::Get(
                            array('_articleRecordId' => array_unique($repostArticleRecordsIds)),
                            array(BaseFactory::WithoutPages => true)
                        );
                        $repostArticleRecords = BaseFactoryPrepare::Collapse($repostRecords, 'articleRecordId', false);
                    }
                }
            }
        }

        Response::setArray('repostArticleRecords', $repostArticleRecords);
        Response::setArray('articleRecords', $articleRecords);
        Response::setArray('articlesQueue', $articlesQueues);
        Response::setObject('queueDate', $queueDate);

        if ($isHistory) {
            Page::$TemplatePath = 'tmpl://fe/elements/articles-queue-history.tmpl.php';
        } else if ($type == GridLineUtility::TYPE_ALL) {
            Page::$TemplatePath = 'tmpl://fe/elements/articles-queue-view.tmpl.php';
        } else {
            $this->setGrid($targetFeedId, $date, $type, $articlesQueues);
        }
    }

    private function setGrid($targetFeedId, $date, $type, $articlesQueue) {
        $now = DateTimeWrapper::Now();

        $grid = GridLineUtility::GetGrid($targetFeedId, $date, $type);

        if(!empty($articlesQueue)) {
            foreach($articlesQueue as $articlesQueueItem) {
                //ищем место в grid для текущей $articlesQueueItem
                $place = null;
                foreach ($grid as $key => $gridItem) {
                    if ($gridItem['dateTime'] >= $articlesQueueItem->startDate && $gridItem['dateTime'] <= $articlesQueueItem->endDate) {
                        if (empty($gridItem['queue'])) {
                            $place = $key;
                        }
                    }
                }

                if ($place !== null) {
                    $grid[$place]['queue'] = $articlesQueueItem;
                    $grid[$place]['blocked'] = ($articlesQueueItem->statusId != 1 || $articlesQueueItem->endDate <= $now);
                    $grid[$place]['failed'] = ($articlesQueueItem->statusId != StatusUtility::Finished && $articlesQueueItem->endDate <= $now);
                }
            }
        }

        Response::setArray( 'grid', $grid );
    }
}
?>
