<?php

/**
 * ProcessQueue Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */

class ProcessQueue {

    public $articleIdsForMurituri = [
        '11477382' => true,
        '11989820' => true,
        '12588385' => true,
    ];

    public function Execute() {
        set_time_limit(0);
        Logger::LogLevel(ELOG_DEBUG);

        ConnectionFactory::BeginTransaction();

            ConnectionFactory::BeginTransaction();

            //атомарно занимаем запись в очереди
            //берем только ту запись, которую нужно отправить по времени
            //валера, твое время настало!
            $sql = <<<sql
                SELECT * FROM "articleQueues"
                WHERE "statusId" = 1
                AND @now >= "startDate"
                AND @now <= "endDate"
                AND "createdAt" > now() - interval '30 day'
                LIMIT 1 FOR UPDATE;
sql;

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $cmd->SetDateTime('@now', DateTimeWrapper::Now());

        $ds         = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        while ($ds->next() ) {
            $object = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
        }

        if (!empty($object)) {
            $object->statusId = StatusUtility::Queued;
            ArticleQueueFactory::UpdateByMask($object, array('statusId'), array('articleQueueId' => $object->articleQueueId) );
            ConnectionFactory::CommitTransaction(true);
        } else {
            ConnectionFactory::CommitTransaction(true);
            return;
        }

        $this->sendArticleQueue($object);
    }

    /**
     * @param ArticleQueue $articleQueue
     * @return bool
     */
    private function sendArticleQueue($articleQueue) {
        $result = false;
        //select objects
        $targetFeed = TargetFeedFactory::GetById($articleQueue->targetFeedId, array(), array(BaseFactory::WithLists => true));
        $articleRecord = ArticleRecordFactory::GetOne(
            array('articleQueueId' => $articleQueue->articleQueueId)
        );

        //check for article options
        ArticleFactory::$mapping['view'] = ArticleFactory::$mapping['table'];
        $article = ArticleFactory::GetById($articleQueue->articleId, array(), array(BaseFactory::WithoutDisabled => false));
        $sourceFeed = SourceFeedFactory::GetOne(array('sourceFeedId' => $article->sourceFeedId));
        $sourceFeed = !empty($sourceFeed) ? $sourceFeed : new SourceFeed();

        if ($targetFeed->type == TargetFeedUtility::FB) {
            if (empty($targetFeed) || empty($articleRecord)) {
                return false;
            }

            $this->sendPostToFb($targetFeed, $articleQueue, $articleRecord);
        }

        if ($targetFeed->type == TargetFeedUtility::VK ) {
            if (empty($targetFeed)  || empty($articleRecord)) {
                return false;
            }
            $roles = array();

            $tokens  = [];
            $tokens2 = [];
            //если у статьи есть автор - берем токен для него. если для этого автора надо постить с ботов -
            // берем их токены
            if ( $articleQueue->author ) {
                $tokens = AccessTokenUtility::getTokens( $articleQueue->author, $targetFeed);
            }

            //на всякий случай, для наших пабликов все равно берем токены ботов. мало ли...
            $userFeeds  =  UserFeedFactory::Get(array( 'targetFeedId' => $targetFeed->targetFeedId ));
            $userFeeds  =  ArrayHelper::Collapse( $userFeeds, 'vkId', $convertToArray = false);
            $vkIds = array_keys($userFeeds);
            if (!empty($vkIds)) {
                $botAuthors =  AuthorFactory::Get( array(
                    'vkIdIn' => $vkIds,
                    'isBot' =>  true
                ));

                if (!empty($botAuthors)) {

                    $botAuthors =  ArrayHelper::Collapse( $botAuthors, 'vkId', $convertToArray = false);
                    $tokens2     =  AccessTokenFactory::Get(array(
                        'vkIdIn' => array_keys( $botAuthors ),
                        'version' => AuthVkontakte::$Version
                    ));
                }
            }

            foreach($tokens2 as $t) {
                $tokens[] = $t;
            }

            //добавляем в начало массива токенов токены смертников для конкретных постов
            if ( isset($this->articleIdsForMurituri[ $articleQueue->articleId ])) {
                $tokens = array_merge( AccessTokenUtility::getMorituri(), $tokens );
            }


            foreach ($tokens as $token) {
                if(!isset($token->accessToken))
                    continue;
                try {
                    $this->sendPostToVk($sourceFeed, $targetFeed, $articleQueue, $articleRecord, $token->accessToken, $article);
                    return true;
                } catch (Exception $Ex) {
                    Logger::Warning($Ex->getMessage());
                }
            }
            $this->restartArticleQueue($articleQueue);
            $err = 'Failed to post, persumably publishers are banned, public id = ' . $targetFeed->externalId;
            Logger::Warning($err);

            AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);
            return true;
        }
    }

    /**
     * @param SourceFeed $sourceFeed
     * @param TargetFeed $targetFeed
     * @param ArticleQueue $articleQueue
     * @param ArticleRecord $articleRecord
     * @param Article $article
     */
    private function sendPostToVk($sourceFeed, $targetFeed, $articleQueue, $articleRecord, $token, $article) {
        $isWithSmallPhoto = ArticleUtility::IsTopArticleWithSmallPhoto($sourceFeed, $articleRecord);
        if ($isWithSmallPhoto) {
            $articleRecord->photos = array();
        }

        $link = $articleRecord->link;
        if (strpos($link, 'topface.com') !== false) {
            $link .= '?ref=pub';
        }
        $post_data = array(
            'text' => $articleRecord->content,
            'group_id' => $targetFeed->externalId,
            'vk_access_token' => $token,
            'photo_array' => array(),
            'audio_id' => array(),
            'video_id' => array(),
            'link' => $link,
            'header' => '',
            'repost_post' => $articleRecord->repostExternalId
        );

        if (!empty($articleRecord->photos)) {
            /**
             * РѕС‚РїСЂР°РІР»СЏРµРј РЅРµ Р±РѕР»РµРµ 10 С„РѕС‚РѕРє
             */
            $articlePhotos = array_slice($articleRecord->photos, 0, 10);
            foreach ($articlePhotos as $photoItem) {
                $remotePath = MediaUtility::GetArticlePhoto($photoItem);
                $localPath  = Site::GetRealPath('temp://upl_' . md5($remotePath) . '.jpg');
                $localPath2 = Site::GetRealPath('temp://upl_' . md5($remotePath . 'DDADade') . '.jpg');
                try {
                    file_put_contents($localPath, file_get_contents($remotePath));
                } catch( Exception $Ex ) {
                    AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                        'failed get photo from vk( ' . $remotePath . '): ' . $Ex->getMessage());
                    throw $Ex;
                }
                $fs = filesize( $localPath) / 1024 ;
                if ( $fs < 3 )  {
                    AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                        'damaged photo ' . $remotePath . ' .Its size = ' . $fs . 'kb' );
                    unlink( $localPath);
                    try {
                        file_put_contents($localPath2, file_get_contents($remotePath));
                    } catch( Exception $Ex ) {
                        AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                            'failed get photo from vk( ' . $remotePath . '): ' . $Ex->getMessage());
                        throw $Ex;
                    }
                    $post_data['photo_array'][] = $localPath2;
                } else {
                    $post_data['photo_array'][] = $localPath;
                }

            }
        }

        $sender = new SenderVkontakte($post_data);

        try {
            if ( !$post_data['repost_post'] ) {
                $articleQueue->externalId = $sender->send_post();
            } else {
                $articleQueue->externalId = $sender->repost();
            }
            //Р·Р°РєСЂС‹РІР°РµРј
            $this->finishArticleQueue($articleQueue);

            if( $article->isSuggested ) {
                try {
                    $sender->delete_post( $article->externalId );
                } catch( Exception $e ) {

                    AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                        'cant delete post (' . $article->externalId . ' ) ' .  $e->getMessage() );

                }
            }

            if ($article->sourceFeedId == SourceFeedUtility::FakeSourceTopface) {
                TopfaceUtility::AcceptPost($article, $articleRecord, $articleQueue->externalId);
            }
        } catch (ChangeSenderException $Ex){
            AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId,
                'failed to post from token' . $token . ' ' . $Ex->getMessage());
            throw $Ex;
        } catch (Exception $Ex){
            $err = $Ex->getMessage();
            Logger::Warning($err);
            AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);
            throw $Ex;
        }

        //unlink temp files
        if (!empty($post_data['photo_array'])) {
            foreach ($post_data['photo_array'] as $localPath) {
                @unlink($localPath);
            }
        }

    }

    /**
     * @param TargetFeed $targetFeed
     * @param ArticleQueue $articleQueue
     * @param ArticleRecord $articleRecord
     */
    private function sendPostToFb($targetFeed, $articleQueue, $articleRecord) {
        $fields_arr = array(
            'album'         => $targetFeed->externalId,
            'access_token'  => $targetFeed->params['token'],
            'source'        => null,
            'message'       => $articleRecord->content,
            'targeting'     => array()
        );

        if (!empty($articleRecord->photos)) {
            $photoItem = current($articleRecord->photos);

            $remotePath = MediaUtility::GetArticlePhoto($photoItem);
            $localPath  = Site::GetRealPath('temp://upl_' . md5($remotePath) . '.jpg');

            file_put_contents($localPath, file_get_contents($remotePath));

            $fields_arr['source'] = '@' . $localPath;
        } else {
            AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, 'Article has no photos');
            return;
        }

        try {
            $sender = new SenderFacebook(json_encode($fields_arr));
            $result = $sender->send_photo();
            Logger::Info($result);

            //Р·Р°РєСЂС‹РІР°РµРј
            $this->finishArticleQueue($articleQueue);
        } catch (Exception $Ex) {
            $err = $Ex->getMessage();
            Logger::Warning($err);

            AuditUtility::CreateEvent('exportErrors', 'articleQueue', $articleQueue->articleQueueId, $err);

            //СЃС‚Р°РІРёРј РѕР±СЂР°С‚РЅРѕ РІ РѕС‡РµСЂРµРґСЊ
            $this->restartArticleQueue($articleQueue);
        }

        //unlink temp files
        @unlink($localPath);
    }

    private function finishArticleQueue($articleQueue) {
        $articleQueue->statusId = StatusUtility::Finished;
        $articleQueue->sentAt = DateTimeWrapper::Now();
        ArticleQueueFactory::UpdateByMask($articleQueue, array('statusId', 'sentAt', 'externalId'), array('articleQueueId' => $articleQueue->articleQueueId) );

        $object = ArticleFactory::GetById($articleQueue->articleId, array(), array(BaseFactory::WithoutDisabled => false));

        if (!empty($object->authorId)) {
            $object->queuedAt = null;
            $object->sentAt = DateTimeWrapper::Now();
            ArticleFactory::UpdateByMask($object, array('sentAt', 'queuedAt'), array('articleId' => $object->articleId));
            AuthorEventUtility::EventSent($object);
        }
    }

    private function restartArticleQueue($articleQueue) {
        $articleQueue->statusId = 1;
        ArticleQueueFactory::UpdateByMask($articleQueue, array('statusId'), array('articleQueueId' => $articleQueue->articleQueueId) );
    }
}
?>
