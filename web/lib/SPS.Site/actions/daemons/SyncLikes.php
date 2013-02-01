<?php
Package::Load( 'SPS.Articles' );
Package::Load( 'SPS.Site' );
Package::Load( 'SPS.VK' );

class SyncLikes {
    const   MODE_LAST_DAY = 'last_day',
            MODE_ALL = 'all',
            MODE_INTERVAL = 'interval',
            GROWTH_LIMIT  = 1.03;
    /**
     * @var Daemon
     */
    private $daemon;

    /**
     * макс. кол-во постов которое будем обрабатывать за один раз
     * @var int
     */
    private $maxArticlesSelectFromQueue = 200;

    public function Execute() {
        set_time_limit(0);
        Logger::LogLevel(ELOG_DEBUG);

        $this->daemon                   = new Daemon();
        $this->daemon->package          = 'SPS.Site';
        $this->daemon->method           = 'SyncLikes';
        $this->daemon->maxExecutionTime = '01:00:00';

        $maxPostsPerRequest = ParserVkontakte::MAX_POST_LIKE_COUNT;
        $parser = new ParserVkontakte();
        $mode = self::MODE_LAST_DAY;
        if (array_key_exists(self::MODE_ALL, $_REQUEST)) {
            $mode = self::MODE_ALL;
        } elseif (array_key_exists(self::MODE_INTERVAL, $_REQUEST)) {
            $mode = self::MODE_INTERVAL;
        }

        //если $filter is true, то слабоизменившиеся записи уходят из проверки
        $filter = false;

        if ($mode == self::MODE_INTERVAL) {
            $interval = strtolower( Request::getString( 'interval' ));
            switch( $interval ){
                case 'minutes':
                    $from = DateTimeWrapper::Now()->sub( new DateInterval('PT10M'));
                    $to   = '';
                    break;
                case 'hour':
                    $from = DateTimeWrapper::Now()->sub( new DateInterval('PT1H'));
                    $to   = DateTimeWrapper::Now()->sub( new DateInterval('PT10M'));
                    break;
                case '3hours':
                    $from = DateTimeWrapper::Now()->sub( new DateInterval('PT3H'));
                    $to   = DateTimeWrapper::Now()->sub( new DateInterval('PT1H'));
                    break;
                case 'day':
                    $from = DateTimeWrapper::Now()->sub( new DateInterval('P1D'));
                    $to   = DateTimeWrapper::Now()->sub( new DateInterval('PT3H'));
                    break;
                case 'week':
                    $from = DateTimeWrapper::Now()->sub( new DateInterval('P1W'));
                    $to   = DateTimeWrapper::Now()->sub( new DateInterval('P1D'));
                    $filter = true;
                    break;
                case 'month':
                    //пока неактивно
                    die();
                    $from   = DateTimeWrapper::Now()->sub( new DateInterval('P1M'));
                    $to     = DateTimeWrapper::Now()->sub( new DateInterval('P1W'));
                    $filter = true;
                    break;
                default:
                    die();
            }
            $articleExternalIds = $this->getIntervalArticles( $filter, $from, $to );
        } elseif( $mode == self::MODE_LAST_DAY ) {
            $from   = DateTimeWrapper::Now()->sub( new DateInterval('P1D'));
            $articleExternalIds = $this->getIntervalArticles( false, $from );
        } elseif( $mode == self::MODE_ALL ) {
            $articleExternalIds = $this->getAllArticles();
        }

        // если ничего не нашли
        if (!$articleExternalIds) return;

        // Получаем лайки, срезая с массива куски размером $maxPostsPerRequest
        $access_token_changes = 0;
        $offset = 0;
        $access_token = VkHelper::get_service_access_token();
        while ($articleExternalIdsSlice = array_slice($articleExternalIds, $offset, $maxPostsPerRequest)) {
            $offset += $maxPostsPerRequest;
            $likes = array();
            try {
                $likes =  $parser->get_post_likes(array_keys($articleExternalIdsSlice), $access_token );
                sleep(1);
            } catch ( AccessTokenIsDead $exception ) {
                $access_token = VkHelper::get_service_access_token();
                $offset -= $maxPostsPerRequest;
                $access_token_changes ++;
                if( $access_token_changes > 20 ) {
                    Logger::Warning('error in SyncLikes: access_tokens are dead');
                    die();
                }
            } catch ( Exception $exception ) {
                Logger::Warning($exception->getMessage());
            }


            if ($likes) {
                foreach ( $likes as $externalId => $values ) {
                    // обновляем запись по articleQueueId, т.к. это поле в индексе
                    if (array_key_exists($externalId, $articleExternalIds)) {
                        $articleQueueId = $articleExternalIds[$externalId]['queueId'];
                        $prevLikes      = $articleExternalIds[$externalId]['prevLikes'];
                        $o = new ArticleQueue();
                        //если рассм. интервал больше 3 дней - исключаем из обновлений слабо подросшие по лайкам посты
                        if ( $filter && $prevLikes && ( $values['likes'] / $prevLikes ) < self::GROWTH_LIMIT  ) {
                            $o->collectLikes = false;
                            ArticleQueueFactory::UpdateByMask($o, array('collectLikes'), array('articleQueueId' => $articleQueueId));
                        } else {
                            $o->externalLikes    = $values['likes'];
                            $o->externalRetweets = $values['reposts'];
                            ArticleQueueFactory::UpdateByMask($o, array('externalLikes', 'externalRetweets'), array('articleQueueId' => $articleQueueId));
                        }
                    } else {
                        // throw Exception  - API вернул странный id поста
                    }
                }
            }
        }
    }

    /**
     * Возвращает статьи за предыдущий день
     * @return array
     */
    private function getLastDayArticles() {
        // со вчера 00-00-00 до 23-59-59
        $DateInterval = new DateInterval('P1D');
        $from = DateTimeWrapper::Now()->setTime(0, 0, 0)->sub($DateInterval);
        $to = DateTimeWrapper::Now()->setTime(23, 59, 59)->sub($DateInterval);

        // загружаем посты постранично, чтобы сильно не жрало память
        $search = array(
            'sentAtFrom' => $from,
            'sentAtTo' => $to,
            'externalIdNot' => '1',
            'externalIdExist' => true,
//            'emptyExternalLikes' => true,
            'pageSize' => $this->maxArticlesSelectFromQueue
        );

        $page = 0;
        $articleExternalIds = array();
        while (true) {
            $search['page'] = $page++;
            // Список постов
            $ArticlesQueues = ArticleQueueFactory::Get($search, array(BaseFactory::WithoutDisabled => false));

            if (!$ArticlesQueues) break;

            foreach ($ArticlesQueues as $ArticleQueue) {
                /** @var $ArticleQueue ArticleQueue */
                // делаем так, чтобы было легко найти articleQueueId при обновлении
                $articleExternalIds[$ArticleQueue->externalId] = $ArticleQueue->articleQueueId;
            }
        }

        return $articleExternalIds;
    }

    /**
     * Возвращает все статьи
     * @return array
     */
    private function getAllArticles() {
        $sql = 'SELECT aq."externalId", aq."articleQueueId" FROM "articles" a
                INNER JOIN "articleQueues" aq USING ("articleId")
                WHERE 1=1
                AND aq."externalId" IS NOT NULL
                AND aq."externalId" != \'1\'
                AND aq."externalLikes" IS NULL
                ORDER BY random()
                LIMIT 3000';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $ds         = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        $articleExternalIds = array();
        while ($ds->next() ) {
            $ArticleQueue = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
            //для совместимости с getIntervalArticles массив вместо числа
            $articleExternalIds[$ArticleQueue->externalId]['queueId'] = $ArticleQueue->articleQueueId;
        }
        return $articleExternalIds;
    }

    private function getIntervalArticles( $filter, $from, $to = 0 )
    {
        if ( !$to )
            $to = DateTimeWrapper::Now();
        // загружаем посты постранично, чтобы сильно не жрало память
        $search = array(
            'sentAtFrom' => $from,
            'sentAtTo' => $to,
            'externalIdNot' => '1',
            'externalIdExist' => true,
            'pageSize' => $this->maxArticlesSelectFromQueue,
            'collectLikes'  =>  true
        );
        $page = 0;
        $articleExternalIds = array();
        while (true) {
            $search['page'] = $page++;
            // Список постов
            $ArticlesQueues = ArticleQueueFactory::Get($search, array(BaseFactory::WithoutDisabled => false));

            if (!$ArticlesQueues) break;

            foreach ($ArticlesQueues as $ArticleQueue) {
                /** @var $ArticleQueue ArticleQueue */
                // делаем так, чтобы было легко найти articleQueueId при обновлении
                //для сравнения  сохраняем результат прошлой проверки лайков
                $articleExternalIds[$ArticleQueue->externalId]['queueId']   = $ArticleQueue->articleQueueId;
                $articleExternalIds[$ArticleQueue->externalId]['prevLikes'] = $ArticleQueue->externalLikes;
            }
        }

        return $articleExternalIds;
    }
}