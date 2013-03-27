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

        if ( $timestamp <  DateTimeWrapper::Now()->getTimestamp()) {
            $result['message'] = 'Too late';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        if (!empty($queueId)) {
            //просто перемещаем элемент очереди
            ArticleUtility::ChangeQueueDates($queueId, $timestamp);

            $result = array(
                'success' => true,
                'id' => $queueId
            );
            echo ObjectHelper::ToJSON($result);
            return true;
        }

        // может ли планировать в ленту
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        if (!$TargetFeedAccessUtility->canAddArticlesQueue($targetFeedId)) {
            $result['message'] = 'AccessDenied!';
            echo ObjectHelper::ToJSON($result);
            return false;
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

            $sqlResult = ArticleRecordFactory::Add($articleQueueRecord);
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
        } else {
            $result['message'] = 'Cant Create Article Queue';
        }
        echo ObjectHelper::ToJSON($result);
    }
}

?>