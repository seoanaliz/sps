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
        $vkPostId = Request::getInteger('vkPostId');
        $checkIfArticle = !empty($vkPostId) || !empty($articleId);
        if ( !$checkIfArticle  || empty($targetFeedId) || empty($timestamp) || empty($type) || empty(GridLineUtility::$Types[$type])) {
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

        $limits_check = ArticleUtility::checkLimitsForFeed( $targetFeedId, $timestamp, $queueId );
        if( $limits_check ) {
            $result['message'] = $limits_check;
            echo ObjectHelper::ToJSON( $result );
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

        $targetFeed = TargetFeedFactory::GetById($targetFeedId);
        // получаем пост
        if( $vkPostId ) {
            $articleArray   = $this->createArticle( $vkPostId, $targetFeed );
            if ( empty( $articleArray)) {
                $result['message'] = 'PostNotFound: vk.com/wall-' . $targetFeed->targetFeedId . '_' . $vkPostId;
                echo ObjectHelper::ToJSON($result);
                return false;
            }
            $article        = $articleArray['article'];
            $articleRecord  = $articleArray['articleRecord'];
        } else {
            $article = ArticleFactory::GetById($articleId);
            $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $articleId));
        }
        if (!$article) {
            $result['message'] = 'ArticleNotFound:' . $articleId;
            echo ObjectHelper::ToJSON($result);
            return false;
        }

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
                array('articleId' => $article->articleId, 'targetFeedId' => $targetFeedId)
            );

            if ($existsCount) {
                $result['message'] = 'articleQueueExists';
                echo ObjectHelper::ToJSON($result);
                return false;
            }
        }

        // добавляем элемент очереди
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
                $repostArticleRecords[$articleQueueRecord->repostArticleRecordId] = $maybeRepostArticleRecord;
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

    /** вернет массив из Article и ArticleRecord
     *@var $targetFeed TargetFeed
     * */
    protected function createArticle( $postVkId, $targetFeed ) {
        $result = false;
        $fullPostId = '-' . $targetFeed->externalId . '_' . $postVkId;
        $token = AccessTokenFactory::Get( array( 'vkId' => AuthVkontakte::IsAuth()));
        if( empty( $token ))
            return $result;
        $token = current( $token)->accessToken;

        try {
            $posts = ParserVkontakte::get_posts_by_vk_id( array( $fullPostId ), $token);
        } catch ( Exception $e) {
            return $result;
        }
        if( !empty( $posts)) {
            $article = ParserVkontakte::get_article_from_post(
                $posts[0],
                $targetFeed->targetFeedId
            );
            $article->isSuggested = true;
        }

        if( !empty($article) && ArticleFactory::Add($article, array( BaseFactory::WithReturningKeys => true ))) {
            $articleRecord = ParserVkontakte::get_articleRecord_from_post( current($posts));
            $articleRecord->articleId = $article->articleId;
            if ( ArticleRecordFactory::Add( $articleRecord, array( BaseFactory::WithReturningKeys => true ))) {
                return array(
                    'article'       =>  $article,
                    'articleRecord' =>  $articleRecord,
                );
            }
        }
        return $result;
    }
}

?>