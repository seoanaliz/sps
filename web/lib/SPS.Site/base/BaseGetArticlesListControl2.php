<?php
/**
 * Конторллер списка постов для Socialboard
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class BaseGetArticlesListControl2 extends BaseGetArticlesListControl {

    protected $isApp = false;

    const MODE_MY = 'my';

    const MODE_ALL = 'all';

    /**
     * Отложенные
     */
    const MODE_DEFERRED = 'deferred';

    /**
     * @var string
     */
    private $articleLinkPrefix = 'http://vk.com/wall-';

    /**
     * @var SourceFeed[]
     */
    private $sourceFeeds = array();

    private $reviewArticleCount = 0;

    protected function getMode(){
        $mode = Request::getString('mode');
        if ($mode == self::MODE_MY) {
            return self::MODE_MY;
        }
        if ($mode == self::MODE_DEFERRED) {
            return self::MODE_DEFERRED;
        }
        return self::MODE_ALL;
    }

    protected function getAuthorsForTargetFeed($targetFeedId) {
        // выираем авторов для этой ленты
        $authorsIds = array();
        $UserFeeds = UserFeedFactory::Get(array('targetFeedId' => $targetFeedId));
        if ($UserFeeds) {
            $vkIds = array();
            foreach ($UserFeeds as $UserFeed){
                $vkIds[] = $UserFeed->vkId;
            }

            $authors = AuthorFactory::Get(
                array(
                    'vkIdIn' => $vkIds
                )
                , array(
                    BaseFactory::WithoutPages => true,
                    BaseFactory::OrderBy => ' "firstName", "lastName" ',
                )
            );

            foreach ($authors as $author){
                $authorsIds[] = $author->authorId;
            }


        }
        return $authorsIds;
    }

    /**
     * Расширение стандартной выборки
     */
    protected function processRequestCustom(){
        parent::processRequestCustom();

        // сортировка
        $sortType = Request::getString('sortType');
        if ($sortType == 'old') {
            $this->options[BaseFactory::OrderBy] = ' "createdAt" ASC, "articleId" ASC ';
        } else if ($sortType == 'best') {
            $this->options[BaseFactory::OrderBy] = ' "rate" DESC, "createdAt" DESC, "articleId" DESC ';
        }

        $type = $this->getSourceFeedType();

        $mode = $this->getMode();
        $targetFeedId = $this->getTargetFeedId();
        if (!$targetFeedId && !$this->isApp) {
            return array('success' => false);
        }

        $role = $this->ArticleAccessUtility->getRoleForTargetFeed($targetFeedId);
        if (is_null($role)) {
            return array('success' => false);
        }


        $author = $this->getAuthor();

        if ($type == SourceFeedUtility::Authors) {
            // в авторских источники не учитываем, хотя они передаются с клиента
            unset($this->search['_sourceFeedId']);


            // количество на рассмотрении и одобренных и не отправленных
            $this->reviewArticleCount = ArticleFactory::Count(array(
                    'authorId' => $author->authorId,
                    'userGroupId' => Request::getInteger('userGroupId'),
                    'targetFeedId' => $targetFeedId,
                ),
                array(
                    BaseFactory::CustomSql => ' AND ( "articleStatus" = ' . PgSqlConvert::ToInt(Article::STATUS_REVIEW) . ' OR ' .
                        '("articleStatus" = ' . PgSqlConvert::ToInt(Article::STATUS_APPROVED) . ' AND "sentAt" IS NULL))',
                    BaseFactory::WithoutPages => true
                ));

            if ($role == UserFeed::ROLE_AUTHOR) {
                if ($mode == self::MODE_MY || $mode == self::MODE_DEFERRED) {
                    $authorsIds = array($author->authorId);
                } else {
                    // если грузим все посты
                    $authorsIds = $this->getAuthorsForTargetFeed($targetFeedId);
                }

                if ($mode != self::MODE_DEFERRED) {
                    $this->options[BaseFactory::CustomSql] = ' AND ("authorId" IN ' . PgSqlConvert::ToList($authorsIds, TYPE_INTEGER) . ' AND "sentAt" IS NOT NULL )  ';
                    $this->options[BaseFactory::WithoutDisabled] = false;
                    $authorsIds = true;
                    unset($this->search['articleStatusIn']);
                } else {
                    $this->options['userGroupId'] = Request::getInteger('userGroupId'); // может быть и null
                    $this->options[BaseFactory::CustomSql] = ' AND ( "articleStatus" = ' . PgSqlConvert::ToInt(Article::STATUS_REVIEW) . ' OR ' .
                        '("articleStatus" = ' . PgSqlConvert::ToInt(Article::STATUS_APPROVED) . ' AND "sentAt" IS NULL))';
                }

            } else {
                if ($mode == self::MODE_MY) {
                    $authorsIds = array($author->authorId);
                } else {
                    $authorsIds = $this->getAuthorsForTargetFeed($targetFeedId);
                }

                //редактору: только одобренные и на рассмотрении записи этой группы
                $articleStatus = Request::getInteger('articleStatus');
                if ($articleStatus) {
                    unset($this->search['articleStatusIn']);
                    $this->search['articleStatus'] = $articleStatus;
                } else {
                    $this->search['articleStatusIn'] = array(Article::STATUS_APPROVED, Article::STATUS_REVIEW);
                }

            }

            if ($authorsIds) {
                if (is_array($authorsIds)) {
                    $this->search['_authorId'] = $authorsIds;
                }
            } else {
                $this->search['_authorId'] = array(-1 => -1);
            }

        } else if ($type == SourceFeedUtility::My) {
            unset($this->search['_sourceFeedId']);
            $this->search['targetFeedId'] = $targetFeedId;
            $this->search['authorId'] = $this->getAuthor()->authorId;
            $this->options[BaseFactory::WithoutDisabled] = false;
            if ($role == UserFeed::ROLE_AUTHOR){
                $articleStatus = Request::getInteger('articleStatus');
                if ($articleStatus){
                    $this->search['articleStatusIn'] = array(Request::getInteger('articleStatus'));
                } else {
                    $this->search['articleStatusIn'] = array(Request::getInteger('articleStatus'));
                }
            } else {
                $this->search['articleStatusIn'] = array(Article::STATUS_APPROVED);
            }

        } else if ($type == SourceFeedUtility::Ads) {
            // рекламка
        }

        if ($type == SourceFeedUtility::Albums) {
            $this->articleLinkPrefix = 'http://vk.com/photo';
        }
    }

    protected function setData() {
        // подгружаем источники
        $this->sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $this->getSourceFeedIds()));

        parent::setData();

        $isWebUserEditor = false;
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId) {
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
            $isWebUserEditor = !is_null($role) && $role != UserFeed::ROLE_AUTHOR;
        }

        Response::setString('articleLinkPrefix', $this->articleLinkPrefix);
        Response::setArray('sourceFeeds', $this->sourceFeeds);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo($this->sourceFeeds));
        Response::setBoolean('isWebUserEditor', $isWebUserEditor);
        Response::setInteger('reviewArticleCount', $this->reviewArticleCount);
        Response::setBoolean('showArticlesOnly', (bool)Request::getBoolean('articlesOnly'));
        Response::setInteger('authorId', $this->getAuthor()->authorId);
    }


    /**
     * Entry Point
     */
    public function Execute()
    {
        $this->processRequest();
        $this->getObjects();
        $this->setData();
    }
}

?>
