<?php
Package::Load('SPS.Site');

/**
 * Конторллер списка постов для Socialboard
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticlesListControl
{

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
     * @var SourceFeed[]
     */
    private $sourceFeeds = array();

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
     * @var string
     */
    private $articleLinkPrefix = 'http://vk.com/wall-';

    /**
     *
     */
    public function __construct(){
        $this->vkId = AuthVkontakte::IsAuth();
    }

    /**
     * Определяем страницу
     * @param string $sessionKey
     */
    protected function processPage($sessionKey = 'page')
    {
        $page = Session::getInteger($sessionKey);
        $page = ($page < 0) ? 0 : $page;
        if (Request::getBoolean('clean')) {
            $page = 0;
        }

        $this->search['pageSize'] = $this->pageSize + 1;
        $this->search['page'] = $page;
    }

    /**
     * Какие типы источников используем
     * @return string|null
     */
    protected function getSourceFeedType(){
        return Request::getString('type');
    }

    /**
     * Возвращает идентификатор запрошеной ленты
     */
    protected function getTargetFeedId(){
        return Request::getInteger('targetFeedId');
    }

    /**
     * Возвращает статус записи
     */
    protected function getArticleStatus(){
        return Request::getInteger('articleStatus');
    }

    /**
     * Возвращает массив источников
     * @return array
     */
    protected function getSourceFeedIds() {
        $sourceFeedIds = Request::getArray('sourceFeedIds');
        return !empty($sourceFeedIds) ? $sourceFeedIds : array();
    }

    /**
     * Обрабатывем запрос
     * @throws Exception
     */
    protected function processRequest()
    {
        // Определяем страницу
        $this->processPage();

        // тип источника
        $sourceFeedType = self::getSourceFeedType();

        // для какой ленты
        // только для ТопФейса и Авторских, т.к. у них это заранее определено
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId && ($sourceFeedType == SourceFeedUtility::Authors || $sourceFeedType == SourceFeedUtility::Topface)){
            $this->search['targetFeedId'] = $targetFeedId;
        }

        // определяем источники
        $sourceFeedIds = $this->getSourceFeedIds();
        if ($sourceFeedIds) {
            $this->search['_sourceFeedId'] = $sourceFeedIds;
        }

        // если запрашиваем авторские посты
        if ($sourceFeedType == SourceFeedUtility::Authors) {
            $ArticleAccessUtility = new ArticleAccessUtility($this->vkId);
            if ($ArticleAccessUtility->hasAccessToSourceType($targetFeedId, $sourceFeedType)) {

                $articleStatuses = $ArticleAccessUtility->getArticleStatusesForTargetFeed($targetFeedId);

                // фильтр по статусам - только для авторских постов
                // если мы запросили определенный статус и он входит в список разрешенных, то берем только его
                $reqArticleStatus = $this->getArticleStatus();
                if ($reqArticleStatus && in_array($reqArticleStatus, $articleStatuses)) {
                    $articleStatuses = array($reqArticleStatus);
                }

                $this->search['articleStatusIn'] = $articleStatuses;
            } else {
                throw new Exception('Access error');
            }

            $this->userRateFilter = false;
        }
        // источник - топфейс
        elseif ($sourceFeedType == SourceFeedUtility::Topface) {
            if (!AccessUtility::HasAccessToTargetFeedId($targetFeedId)) {
                throw new Exception('Access error');
            }
            // в топфейсе не сортируем
            unset($this->search['rateGE'], $this->search['rateLE']);
            // выставляем источники вручную
            $this->search['_sourceFeedId'] = array(SourceFeedUtility::FakeSourceTopface => SourceFeedUtility::FakeSourceTopface);

            $this->userRateFilter = false;
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

        $this->processRequestCustom();
    }


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

        $type = self::getSourceFeedType();

        // не авторские посты
        // if (empty($this->search['_sourceFeedId']) && ($type != SourceFeedUtility::Authors) && ($type != SourceFeedUtility::Topface)) {
        //    $this->search['_sourceFeedId'] = array(-999 => -999);
        //    return;
        // }

        if ($type == SourceFeedUtility::Authors) {
            $this->search['_sourceFeedId'] = array(SourceFeedUtility::FakeSourceAuthors => SourceFeedUtility::FakeSourceAuthors);

            // определяем источники
            $sourceFeedIds = $this->getSourceFeedIds();

            // фильтр источников выступает как фильтр авторов
            if (!empty($sourceFeedIds)) {
                $this->search['_authorId'] = $sourceFeedIds;
            } else {
                $this->search['_authorId'] = array(-1 => -1);
            }
        }

        if ($type == SourceFeedUtility::Albums) {
            $this->articleLinkPrefix = 'http://vk.com/photo';
        }
    }

    /**
     * Загрузка комментариев
     */
    protected function loadComments(){
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

        if ($this->hasMore) {
            Session::setInteger('page', $this->search['page'] + 1);
        }
    }

    protected function setData()
    {
        Response::setArray('articles', $this->articles);
        Response::setArray('articleRecords', $this->articleRecords);
        Response::setInteger('articlesCount', $this->articlesCount);
        Response::setBoolean('hasMore', $this->hasMore);
        Response::setArray('authors', $this->authors);
        Response::setArray('commentsData', $this->commentsData);
        Response::setString('articleLinkPrefix', $this->articleLinkPrefix);
    }

    /**
     * Entry Point
     */
    public function Execute()
    {
        $this->processRequest();
        $this->getObjects();

        // подгружаем источники
        $this->sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $this->getSourceFeedIds()));
        Response::setArray('sourceFeeds', $this->sourceFeeds);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo($this->sourceFeeds));

        $this->setData();
    }
}

?>