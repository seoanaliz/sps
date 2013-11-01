<?php

class PostSetProtectedControl extends BaseControl {
    const INTERVAL_BETWEEN_MOVED_POSTS = 300;
    private $contentTypeName = array(
        'photo' =>  'pid',
        'video' =>  'vid',
        'audio' =>  'aid',
        'doc'   =>  'did',
        'poll'  =>  'poll_id',
    );

    public function Execute() {

        $result = array();
        $articleQueueId = Request::getInteger('queueId');
        $time = Request::getString('time');

        if (is_null($articleQueueId) || is_null($time)) {
            $result['success'] = false;
            $result['message'] = 'Need more data';
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $articleQueue = ArticleQueueFactory::GetById($articleQueueId);
        if ( !$articleQueue ) {
            $result['success'] = false;
            $result['message'] = 'Database error';
            die( ObjectHelper::ToJSON($result));
        }

        //проверим права доступа юзера
        if (!$TargetFeedAccessUtility->canCreatePlanDeletePost($articleQueue->targetFeedId)) {
            $result['success'] = false;
            $result['message'] = 'Access Denied';
            die(ObjectHelper::ToJSON($result));
        }

        if ($time == '00:00') {
            //убираем защиту
            $result['success'] = $this->removeProtection($articleQueue);
            if (!$result['success'])
                $result['message'] = 'Database error';
        } else {
            list($hour, $minutes) = explode(':', $time);
            $ts = $articleQueue->startDate->getTimestamp();
            $protectTo = new DateTimeWrapper(null);
            $protectTo->setTimestamp($ts)->modify('+' . $hour . ' hours')
                ->modify('+' . $minutes . ' minutes')
                ->modify('-30 seconds');
            if ( $hour > 5 || $protectTo <= $articleQueue->startDate ) {
                $result['message'] = 'Wrong protect time';
                die(ObjectHelper::ToJSON($result));
            }
            //если меняем время
            if ( $articleQueue->protectTo != null ) {
                $this->removeProtection($articleQueue);
            }

            if ( $this->checkProtect($articleQueue->startDate, $protectTo, $articleQueue->targetFeedId )) {
                $result['message'] = 'In protected interval';
                die(ObjectHelper::ToJSON($result));
            }
            $articleQueue->protectTo = $protectTo;
            ArticleQueueFactory::UpdateByMask($articleQueue, array('protectTo'), array('articleQueueId' => $articleQueueId));

            //ставим всем остальным записям в этом периоде статус "неотправлена"
            ArticleUtility::setAQStatus($articleQueue->startDate, $protectTo, StatusUtility::Finished, $articleQueue->targetFeedId );

            $targetFeed = TargetFeedFactory::GetById($articleQueue->targetFeedId);
            $skipPostIds = [];
            // если у поста уже есть id - значит, он может быть отложенным, и его двигать не надо
            if ( !empty( $articleQueue->externalId )) {
                $skipPostIds[] = $articleQueue->externalId;
            }

            $this->clearVkPostponed(
                $targetFeed,
                $articleQueue->startDate->format('U'),
                $protectTo->format('U'),
                $skipPostIds
            );

            $result['success'] = true;
        }

        echo ObjectHelper::ToJSON($result);
    }

    //return true if post intersects with another protection
    public function checkProtect( $protectStartTime, $protectEndTime, $targetFeedId) {
        $sql = 'SELECT * FROM "getArticleQueues" where
                 "protectTo" IS NOT NULL AND
                 "targetFeedId" = @targetFeedId AND
                ( @pst     BETWEEN "startDate" AND "protectTo" OR
                  @pet     BETWEEN "startDate" AND "protectTo" OR
                 "startDate"  BETWEEN @pst AND @pet
                )';
        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetDateTime( '@pst', $protectStartTime );
        $cmd->SetDateTime( '@pet', $protectEndTime );
        $cmd->SetInt( '@targetFeedId',  $targetFeedId );
        $ds = $cmd->Execute();
        return (bool)$ds->GetSize();
    }

    /** @var $targetFeed TargetFeed */
    public function clearVkPostponed( $targetFeed, $fromTs, $toTs, $skipPostIds = [] ) {
        $author = $this->getAuthor();
        $tokens = AccessTokenUtility::getTokens( $author->vkId, $targetFeed);
        $params = array(
            'owner_id'  =>  '-' . $targetFeed->externalId,
            'filter'    =>  'postponed',
            'count'     =>  50,
            'v'         =>  '5.2'
        );
        $postponedPosts  = array();
        foreach( $tokens as $token )  {
            try {
                $params['access_token'] = $token->accessToken;
                $postponedPosts = VkHelper::api_request( 'wall.get', $params );
                break;
            } catch( Exception $e ) {
            }
        }
        $postsForDelete = array();
        // выбираем посты для сдвига, проверяем, свободно ли место под эти посты,если нет - двигаем и эти
        $counter = 0;
        foreach ( $postponedPosts->items as $post ) {
            if (!isset($post->date) ||
                !($fromTs <= $post->date && $post->date <= $toTs + $counter * self::INTERVAL_BETWEEN_MOVED_POSTS ) || //лежит ли в интервале проверки
                in_array( $skipPostIds, $post->to_id . '_' . $post->id) //нужно ли двигать пост с этим id
            ) {
                continue;
            }

            $postsForDelete[] = $post;
            $counter ++;
        }

        $postsForDelete = array_reverse( $postsForDelete );
        if( !empty($postsForDelete)) {
            $res = $this->movePosts($postsForDelete, $tokens, $toTs);
        }
        return;
    }

    //составляет execute код для vk и отправляет его на выполнение. смещает посты на конец периода защиты, с интервалом в 5 минут
    public function movePosts( $posts, $tokens, $endProtectTs ) {
        $code = '';
        $endProtectTs += self::INTERVAL_BETWEEN_MOVED_POSTS;
        foreach( $posts as $post ) {
            $rtsPost = array();
            $rtsAttachments = array();
            if (isset( $post->attachments )) {
                foreach( $post->attachments as $attach) {
                    if ( $attach->type == 'link' ) {
                        $rtsAttachments[] = $attach->link->url;
                        continue;
                    }
                    $type =  $attach->type;
                    $idName = $this->contentTypeName[$type];
                    $rtsAttachments[] = $attach->type . $attach->$type->owner_id . '_' . $attach->$type->$idName;
                }
            }
            $rtsPost['message']     = $post->text;
            $rtsPost['post_id']     = $post->id;
            $rtsPost['owner_id']    = $post->to_id;
            $rtsPost['from_group']  = 1;
            $rtsPost['publish_date']= $endProtectTs;
            if( !empty($rtsAttachments))
                $rtsPost['attachments'] = implode( ',', $rtsAttachments );
            $code   .=  'API.wall.edit(' . json_encode( $rtsPost, JSON_UNESCAPED_UNICODE ) . ');';
            $endProtectTs += self::INTERVAL_BETWEEN_MOVED_POSTS;
        }

        $res = false;
        foreach( $tokens as $token ) {
            $params = array('code'=> $code, 'access_token' => $token->accessToken );
            try {
                $res = VkHelper::api_request('execute', $params );
                break;
            } catch( Exception $e) {
                //print_r($e->getMessage());
            }
        }
        return (bool)$res;
    }

    public function removeProtection( $articleQueue ) {
        if ( !$articleQueue->protectTo ) return true;

        $res = ArticleUtility::setAQStatus($articleQueue->startDate, $articleQueue->protectTo, StatusUtility::Enabled, $articleQueue->targetFeedId );
        if ( $res ) {
            $articleQueue->protectTo = null;
            $res = ArticleQueueFactory::UpdateByMask($articleQueue, array('protectTo'), array('articleQueueId' => $articleQueue->articleQueueId));
        }
        return $res;
    }


}
?>
