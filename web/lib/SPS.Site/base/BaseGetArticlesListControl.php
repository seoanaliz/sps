<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prog-31
 * Date: 13.12.12
 * Time: 13:47
 * To change this template use File | Settings | File Templates.
 */
abstract class BaseGetArticlesListControl extends BaseControl
{

    protected $isApp = false;

    const MODE_MY = 'my';

    const MODE_ALL = 'all';

    /**
     * Отложенные
     */
    const MODE_DEFERRED = 'deferred';

    /**
     * Отправленные
     */
    const MODE_POSTED = 'posted';

    /**
     * @var string
     */
    private $articleLinkPrefix = 'http://vk.com/wall-';

    /**
     * @var SourceFeed[]
     */
    private $sourceFeeds = array();

    private $reviewArticleCount = 0;

    /**
     * @var Article[]
     */
    protected $articles = array();

    /**
     * @var ArticleRecord[]
     */
    protected $articleRecords = array();

    /**
     * @var TargetFeed[]
     */
    protected $targetFeeds = array();

    /**
     * @var Author[]
     */
    protected $authors = array();

    /**
     * @var array
     */
    protected $commentsData = array();

    /**
     * @var int
     */
    protected $pageSize = 20;

    /**
     * @var int
     */
    protected $articlesCount = 0;

    /**
     * @var bool
     */
    protected $hasMore = false;

    /**
     * @var array
     */
    protected $search = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Использовать фильтр по рейтингу
     * @var bool
     */
    protected $userRateFilter = true;

    /**
     * Нужна ли лента в запросе
     * @var bool
     */
    protected $needTargetFeed = true;

    /**
     * @var ArticleAccessUtility
     */
    protected $ArticleAccessUtility;

    protected function getMode(){
        $mode = Request::getString('mode');
        if ($mode == self::MODE_MY) {
            return self::MODE_MY;
        }
        if ($mode == self::MODE_POSTED) {
            return self::MODE_POSTED;
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

    public function __construct()
    {
        parent::__construct();
        $this->ArticleAccessUtility = new ArticleAccessUtility($this->vkId);
    }

    /**
     * Определяем страницу
     * @param string $sessionKey
     */
    protected function processPage($sessionKey = 'page')
    {
        $page = Request::getInteger($sessionKey);
        $page = ($page < 0) ? 0 : $page;
        $this->search['pageSize'] = $this->pageSize + 1;
        $this->search['page'] = $page;
    }


    /**
     * Какие типы источников используем
     * @return string|null
     */
    protected function getSourceFeedType()
    {
        return Request::getString('type');
    }

    /**
     * Возвращает идентификатор запрошеной ленты
     */
    protected function getTargetFeedId()
    {
        return Request::getInteger('targetFeedId');
    }

    /**
     * Возвращает массив источников
     * @return array
     */
    protected function getSourceFeedIds()
    {
        $sourceFeedIds = Request::getArray('sourceFeedIds');
        return !empty($sourceFeedIds) ? $sourceFeedIds : array();
    }

    /**
     * Возвращает статус записи
     */
    protected function getArticleStatus()
    {
        return Request::getInteger('articleStatus');
    }

    /**
     * Обрабатывем запрос
     * @throws Exception
     */
    protected function processRequest()
    {
        $useSourceFilter = $useArticleStatusFilter = false;

        // Определяем страницу
        $this->processPage();

        // тип источника
        $sourceFeedType = $this->getSourceFeedType();

        $mode = $this->getMode();

        // для какой ленты
        // только для ТопФейса и Авторских, т.к. у них это заранее определено
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId && ($sourceFeedType == SourceFeedUtility::Authors || $sourceFeedType == SourceFeedUtility::Topface)) {
            $this->search['targetFeedId'] = $targetFeedId;
        }

        // если запрашиваем авторские посты
        if ($sourceFeedType == SourceFeedUtility::Authors) {

            if ($this->ArticleAccessUtility->hasAccessToSourceType($targetFeedId, $sourceFeedType)) {
                $useArticleStatusFilter= true;
            } else {
                throw new Exception('Access error');
            }

            $this->userRateFilter = false;
        } // источник - топфейс
        elseif ($sourceFeedType == SourceFeedUtility::Topface) {
            // TODO узнать - только проверить доступ?
            if (!$this->ArticleAccessUtility->hasAccessToTargetFeed($targetFeedId)) {
                throw new Exception('Access error');
            }

            $this->userRateFilter = false;
        } elseif ($sourceFeedType == SourceFeedUtility::My) {
            $this->userRateFilter = false;
            $this->search['authorId'] = $this->getAuthor()->authorId;
            $useArticleStatusFilter = true;
            $this->needTargetFeed = $mode != self::MODE_POSTED;
        } elseif ($sourceFeedType == SourceFeedUtility::Source) {
            $useSourceFilter = true;
        } elseif ($sourceFeedType == SourceFeedUtility::Albums) {
            $useSourceFilter = true;
        } elseif ($sourceFeedType == SourceFeedUtility::Ads) {
            $useSourceFilter = true;
        }

        // фильтр по рейтингу
        if ($this->userRateFilter) {
            $from = Request::getInteger('from');
            $to = Request::getInteger('to');

            if ($from !== null) {
                $this->search['rateGE'] = $from;
            }
            if ($to !== null && $to < 100) {
                $this->search['rateLE'] = $to;
            }
        }

        // фильтр по статусам статей
        if ($useArticleStatusFilter) {
            $articleStatuses = $this->ArticleAccessUtility->getArticleStatusesForTargetFeed($targetFeedId);

            // фильтр по статусам - только для авторских постов
            // если мы запросили определенный статус и он входит в список разрешенных, то берем только его
            $reqArticleStatus = $this->getArticleStatus();
            if ($reqArticleStatus && in_array($reqArticleStatus, $articleStatuses)) {
                $articleStatuses = array($reqArticleStatus);
            }

            $this->search['articleStatusIn'] = $articleStatuses;
        }


        // фильтр по источникам
        // определяем источники
        if ($useSourceFilter) {
            $sourceFeedIds = $this->getSourceFeedIds();
            if ($sourceFeedIds) {
                $this->search['_sourceFeedId'] = $sourceFeedIds;
            } else {
                $this->search['_sourceFeedId'] = array(-999 => -999);
            }
        }

        $userGroupId = Request::getInteger('userGroupId');
        if ($userGroupId) {
            $this->search['userGroupId'] = $userGroupId;
        }

        $this->processRequestCustom();
    }

    /**
     * Функция, расширяющая условия выборки
     * @return mixed
     */
    /**
     * Расширение стандартной выборки
     */
    protected function processRequestCustom(){

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
        if ($this->needTargetFeed  && !$targetFeedId && !$this->isApp) {
            return array('success' => false);
        }


        // если мы юзаем ленту то у нас должна быть роль
        $role = null;
        if ($this->needTargetFeed) {
            $role = $this->ArticleAccessUtility->getRoleForTargetFeed($targetFeedId);
            if (is_null($role)) {
                return array('success' => false);
            }
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


            if ($mode == self::MODE_POSTED) {
                /**
                 * Мои отправленные
                 */
                $authorId = Request::getInteger('authorId');
                if (is_numeric($authorId) && $authorId){
                    $this->search['authorId'] = $authorId;
                }
                unset($this->search['articleStatusIn']);
                $this->options[BaseFactory::CustomSql] = ' AND "sentAt" IS NOT NULL';
                $this->options[BaseFactory::WithoutDisabled] = false;
            } else {
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
            }

        } else if ($type == SourceFeedUtility::Ads) {
            // рекламка
        }

        if ($type == SourceFeedUtility::Albums) {
            $this->articleLinkPrefix = 'http://vk.com/photo';
        }
    }

    /**
     * Загрузка комментариев
     */
    protected function loadComments()
    {
        if ($this->articles) {
            $this->commentsData = CommentUtility::GetLastComments(array_keys($this->articles));
        }
    }

    /**
     * Получаем объекты
     * articles - список статей
     * articlesCount - общее количестово статей
     * hasMore - можно ли достать еще
     * articleRecords - записи
     * authors - авторы
     * commentsData - комменты
     */
    protected function getObjects()
    {
        $this->articles = ArticleFactory::Get($this->search, $this->options);
        $this->articlesCount = ArticleFactory::Count($this->search, $this->options + array(BaseFactory::WithoutPages => true));

        $this->hasMore = (count($this->articles) > $this->pageSize);
        $this->articles = array_slice($this->articles, 0, $this->pageSize, true);

        // load articles records
        if (!empty($this->articles)) {
            $this->articleRecords = ArticleRecordFactory::Get(
                array('_articleId' => array_keys($this->articles))
            );

            if (!empty($this->articleRecords)) {
                $this->articleRecords = BaseFactoryPrepare::Collapse($this->articleRecords, 'articleId', false);
            }

            // получаем авторов
            $authorIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('authorId'));
            if (!empty($authorIds)) {
                $this->authors = AuthorFactory::Get(
                    array('_authorId' => array_unique($authorIds)),
                    array(BaseFactory::WithoutPages => true)
                );
            }
        }

        $this->loadComments();
    }

    /**
     *
     */
    protected function setData()
    {
        // подгружаем источники
        $this->sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $this->getSourceFeedIds()));
        Response::setArray('articles', $this->articles);
        Response::setArray('articleRecords', $this->articleRecords);
        Response::setInteger('articlesCount', $this->articlesCount);
        Response::setBoolean('hasMore', $this->hasMore);
        Response::setArray('authors', $this->authors);
        Response::setArray('commentsData', $this->commentsData);
        $isWebUserEditor = false;
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId) {
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
            $isWebUserEditor = !is_null($role) && $role != UserFeed::ROLE_AUTHOR;
        }

        Response::setString('articleLinkPrefix', $this->articleLinkPrefix);
        Response::setString('sourceFeedType', $this->getSourceFeedType());
        Response::setArray('sourceFeeds', $this->sourceFeeds);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo($this->sourceFeeds));
        Response::setBoolean('isWebUserEditor', $isWebUserEditor);
        Response::setInteger('reviewArticleCount', $this->reviewArticleCount);
        Response::setBoolean('showArticlesOnly', (bool)Request::getBoolean('articlesOnly'));
        Response::setInteger('authorId', $this->getAuthor()->authorId);
        Response::setBoolean('forceDisabledPublishing', $this->getSourceFeedType() == 'my');
    }
}
