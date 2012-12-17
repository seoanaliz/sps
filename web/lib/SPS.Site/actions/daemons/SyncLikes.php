<?php
Package::Load( 'SPS.Articles' );
Package::Load( 'SPS.Site' );
Package::Load( 'SPS.VK' );

class SyncLikes {
    const   MODE_LAST_DAY = 'last_day',
            MODE_ALL = 'all';
    /**
     * @var Daemon
     */
    private $daemon;

    /**
     * макс. кол-во постов которое будем обрабатывать за один раз
     * @var int
     */
    private $maxArticlesSelectFromQueue = 2000;

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
        }

        if ($mode == self::MODE_ALL) {
            $articleExternalIds = $this->getAllArticles();
        } else {
            $articleExternalIds = $this->getLastDayArticles();
        }

        // если ничего не нашли
        if (!$articleExternalIds) return;

        // Получаем лайки, срезая с массива куски размером $maxPostsPerRequest
        $offset = 0;
        while ($articleExternalIdsSlice = array_slice($articleExternalIds, $offset, $maxPostsPerRequest)) {
            $offset += $maxPostsPerRequest;
            $likes = array();
            try {
                $likes =  $parser->get_post_likes(array_keys($articleExternalIdsSlice));
                sleep(1);
            } catch (Exception $exception){
                Logger::Warning($exception->getMessage());
            }
            if ($likes) {

                foreach ($likes as $externalId => $values) {
                    // обновляем запись по articleQueueId, т.к. это поле в индексе
                    if (array_key_exists($externalId, $articleExternalIds)) {
                        $articleQueueId = $articleExternalIds[$externalId];
                        $o = new ArticleQueue();
                        $o->externalLikes = $values['likes'];
                        $o->externalRetweets = $values['reposts'];
                        ArticleQueueFactory::UpdateByMask($o, array('externalLikes', 'externalRetweets'), array('articleQueueId' => $articleQueueId));
                    } else {
                        // throw Exception  - API вернул странный id поста
                    }
                }
            }
        }
    }

    /**
     * Возвращает статьи за предидущий день
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
                AND aq."exte  rnalLikes" IS NULL
                ORDER BY random()
                LIMIT 3000';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
        $ds         = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        $articleExternalIds = array();
        while ($ds->next() ) {
            $ArticleQueue = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
            $articleExternalIds[$ArticleQueue->externalId] = $ArticleQueue->articleQueueId;
        }
        return $articleExternalIds;
    }
}