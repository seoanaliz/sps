<?php
/**
 * AddArticleToQueueControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class AddArticleToQueueControl extends BaseControl
{


    /**
     * Entry Point
     */
    public function Execute()
    {
        $result = array(
            'success' => false
        );
        $articleId = Request::getInteger('articleId');
        $targetFeedId = Request::getInteger('targetFeedId');
        $timestamp = Request::getInteger('timestamp');
        $queueId = Request::getInteger('queueId');
        $type = Request::getString('type');

        if (empty($articleId) || empty($targetFeedId) || empty($timestamp) || empty($type) || empty(GridLineUtility::$Types[$type])) {
            $result['message'] = 'Wrong data';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        // может ли планировать в ленту
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        if (!$TargetFeedAccessUtility->canAddArticlesQueue($targetFeedId)) {
            $result['message'] = 'AccessDenied!';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        if ( $timestamp <  DateTimeWrapper::Now()->getTimestamp()) {
            $result['message'] = 'Too late';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        //ограничение по интервалу между постами

        if( ArticleUtility::IsTooCloseToPrevious( $targetFeedId, $timestamp, $queueId )) {
            $result['message'] = 'Time between posts is too small';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        if( ArticleUtility::IsArticlesLimitReached( $targetFeedId, $timestamp )) {
            $result['message'] = 'Too many posts this day';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        if (!empty( $queueId )) {
            //просто перемещаем элемент очереди
            ArticleUtility::ChangeQueueDates( $queueId, $timestamp );

            $result = array(
                'success' => true,
                'id' => $queueId
            );
            $result['html'] = $this->renderArticle(ArticleQueueFactory::GetById($queueId), ArticleRecordFactory::GetOne(array('articleId' => $articleId)));
            echo ObjectHelper::ToJSON($result);
            return true;
        }

        // получаем пост
        $article = ArticleFactory::GetById($articleId);
        if (!$article) {
            $result['message'] = 'ArticleNotFound:' . $articleId;
            echo ObjectHelper::ToJSON($result);
            return false;
        }
        $targetFeed = TargetFeedFactory::GetById($targetFeedId);
        $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));

        if (empty($targetFeed)) {
            $result['message'] = 'Empty Target Feed';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        if (empty($articleRecord)) {
            $result['message'] = 'Empty Article Record';
            echo ObjectHelper::ToJSON($result);
            return false;
        }


        //source feed
        $sourceFeed = SourceFeedFactory::GetById($article->sourceFeedId);
        $sourceFeed = !empty($sourceFeed) ? $sourceFeed : new SourceFeed();

        if ($sourceFeed->type != SourceFeedUtility::Ads) {
            //проверяем, если ли такая $articleId в этой $targetFeedId
            $existsCount = ArticleQueueFactory::Count(
                array('articleId' => $articleId, 'targetFeedId' => $targetFeedId)
            );

            if ($existsCount) {
                $result['message'] = 'articleQueueExists';
                echo ObjectHelper::ToJSON($result);
                return false;
            }
        }

        // добавляем улемент очереди
        $ArticleQueue = new ArticleQueue();
        $ArticleQueue->createdAt = DateTimeWrapper::Now();
        $ArticleQueue->articleId = $article->articleId;
        $ArticleQueue->targetFeedId = $targetFeed->targetFeedId;
        $ArticleQueue->type = $type;
        $ArticleQueue->author = $this->vkId;
        ArticleUtility::BuildDates($ArticleQueue, $timestamp);
        $ArticleQueue->isDeleted = false;
        $ArticleQueue->collectLikes = true;
        $ArticleQueue->deleteAt = null;
        $ArticleQueue->statusId = 1;

        $articleQueueRecord = clone $articleRecord;
        $articleQueueRecord->articleRecordId = null;
        $articleQueueRecord->articleId = null;

        ConnectionFactory::BeginTransaction();

        $sqlResult = ArticleQueueFactory::Add($ArticleQueue, array(BaseFactory::WithReturningKeys => true));

        if ($sqlResult) {
            $articleQueueRecord->articleQueueId = $ArticleQueue->articleQueueId;

            $sqlResult = ArticleRecordFactory::Add($articleQueueRecord, array(BaseFactory::WithReturningKeys => true));
        }

        ConnectionFactory::CommitTransaction($sqlResult);

        if ($sqlResult) {
            if (!empty($article->authorId)) {
                //author event
                AuthorEventUtility::EventQueue($article);
            }

            AuditUtility::CreateEvent(
                'articleQueue'
                , 'article'
                , $article->articleId
                , "Queued by editor VkId " . AuthUtility::GetCurrentUser('Editor')->vkId . ", queueId is " . $articleQueueRecord->articleQueueId);

            $result = array(
                'success' => true,
                'id' => $articleQueueRecord->articleQueueId
            );

            if ($sourceFeed->type != SourceFeedUtility::Ads) {
                //блокируем статью, чтобы ее больше никто не пытался отправить
                $o = new Article();
                $o->statusId = 2;
                $o->queuedAt = DateTimeWrapper::Now();
                ArticleFactory::UpdateByMask($o, array('statusId', 'queuedAt'), array('articleId' => $article->articleId));

                $result['moved'] = true;
            } else {
                $result['moved'] = false;
            }

            $result['html'] = $this->renderArticle($ArticleQueue, $articleQueueRecord);
        } else {
            $result['message'] = 'Cant Create Article Queue';
        }
        echo ObjectHelper::ToJSON($result);
    }

    protected function renderArticle($articleQueueItem, $articleQueueRecord) {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed(Request::getInteger('targetFeedId'));
        if (is_null($role)){
            return '';
        }
        $canEditQueue = ($role != UserFeed::ROLE_AUTHOR);

        $articleRecords = array();
        $articleRecords[$articleQueueItem->articleQueueId] = $articleQueueRecord;
        $articlesQueue = array();
        $articlesQueue[$articleQueueItem->articleQueueId] = $articleQueueItem;

        $repostArticleRecords = array();
        if ($articleQueueRecord->repostArticleRecordId) {
            $maybeRepostArticleRecord = ArticleRecordFactory::GetById($articleQueueRecord->repostArticleRecordId);
            if ($maybeRepostArticleRecord) {
                $repostArticleRecords[$articleRecord->repostArticleRecordId] = $maybeRepostArticleRecord;
            }
        }

        $timestamp = Request::getInteger( 'timestamp' );
        $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);
        $grid = GridLineUtility::GetGrid(Request::getInteger('targetFeedId'), $date, Request::getString('type'));
        $grid = array_reverse( $grid);
        $place = null;
        foreach ($grid as $key => $gridItem) {
            if ($gridItem['dateTime'] >= $articleQueueItem->startDate && $gridItem['dateTime'] <= $articleQueueItem->endDate) {
                if (empty($gridItem['queue'])) {
                    $place = $key;
                    break; // ------------ BREAK
                }
            }
        }

        if ($place !== null) {
            $now = DateTimeWrapper::Now();
            $grid[$place]['queue'] = $articleQueueItem;
            $grid[$place]['blocked'] = ($articleQueueItem->statusId != 1 || $articleQueueItem->endDate <= $now);
            $grid[$place]['failed'] = ($articleQueueItem->statusId != StatusUtility::Finished && $articleQueueItem->endDate <= $now);
            $gridItem = $grid[$place];
        } else {
            return ''; // ---------------- RETURN
        }

        ob_start();
        include Template::GetCachedRealPath('tmpl://fe/elements/articles-queue-list-item.tmpl.php');
        $html = ob_get_clean();
        return $html;
    }
}

?>