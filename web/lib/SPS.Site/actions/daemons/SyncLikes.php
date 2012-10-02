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
        $maxPostsPerRequest = ParserVkontakte::MAX_POST_LIKE_COUNT;

        $parser = new ParserVkontakte();

        $date = DateTimeWrapper::Now()->setDate(2012, 9, 1);

        $from = $date->setTime(0, 0, 0);
        $to = DateTimeWrapper::Now()->setTime(23, 59, 59);

        // Список записей

        $search = array(
            'importedAtFrom' => $from,
            'importedAtTo' => $to,
            'externalIdSet' => true
        );

        ArticleRecordFactory::$mapping['flags']['CanPages'] = true;

        $count = ArticleFactory::Count($search, array(BaseFactory::WithoutDisabled => false));
        $pageCount = ceil($count / $maxPostsPerRequest);
        $page = 0;
        $pageCount = 1;
        $search['pageSize'] = $maxPostsPerRequest;
        while ($page++ < $pageCount) {
            $search['page'] = $page - 1;

            // Список постов
            $Articles = ArticleFactory::Get($search, array(BaseFactory::WithoutDisabled => false));

            $articleExternalIds = array();
            foreach ($Articles as $Article) {
                /** @var $Articles Article */
                $articleExternalIds[] = $Article->externalId;
            }
            print_r($articleExternalIds);
            // Получаем лайки
            $likes =  $parser->get_post_likes($articleExternalIds);
            if ($likes) {
                var_dump($likes);
            }
        }
    }
}