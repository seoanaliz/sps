<?php
Package::Load( 'SPS.Articles' );
Package::Load( 'SPS.Site' );
Package::Load( 'SPS.VK' );

class SyncLikes {

    /**
     * @var Daemon
     */
    private $daemon;

    public function Execute() {
        set_time_limit(0);
        Logger::LogLevel(ELOG_DEBUG);

        $this->daemon                   = new Daemon();
        $this->daemon->package          = 'SPS.Site';
        $this->daemon->method           = 'SyncLikes';
        $this->daemon->maxExecutionTime = '01:00:00';

        // макс. кол-во постов которое будем обрабатывать за один раз
        $maxArticlesSelectFromQueue = 100;
        $maxPostsPerRequest = ParserVkontakte::MAX_POST_LIKE_COUNT;
        $parser = new ParserVkontakte();

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
            'emptyExternalLikes' => true,
            'pageSize' => $maxArticlesSelectFromQueue
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

        // если ничего не нашли
        if (!$articleExternalIds) return;

        // Получаем лайки, срезая с массива куски размером $maxPostsPerRequest
        $offset = 0;
        while ($articleExternalIdsSlice = array_slice($articleExternalIds, $offset, $maxPostsPerRequest)) {
            $offset += $maxPostsPerRequest;
            $likes =  $parser->get_post_likes(array_keys($articleExternalIdsSlice));
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
}