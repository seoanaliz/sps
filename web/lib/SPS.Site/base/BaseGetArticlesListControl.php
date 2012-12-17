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
     * Возвращает массив источников
     * @return array
     */
    protected function getSourceFeedIds() {
        $sourceFeedIds = Request::getArray('sourceFeedIds');
        return !empty($sourceFeedIds) ? $sourceFeedIds : array();
    }

    /**
     * Возвращает статус записи
     */
    protected function getArticleStatus(){
        return Request::getInteger('articleStatus');
    }

    /**
     * Обрабатывем запрос
     * @throws Exception
     */
    protected function processRequest()
    {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        // Определяем страницу
        $this->processPage();

        // тип источника
        $sourceFeedType = $this->getSourceFeedType();

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
        } else {
            $this->search['_sourceFeedId'] = array(-999 => -999);
        }

        // если запрашиваем авторские посты
        if ($sourceFeedType == SourceFeedUtility::Authors) {
            $this->search['_sourceFeedId'] = array(SourceFeedUtility::FakeSourceAuthors => SourceFeedUtility::FakeSourceAuthors);

            $ArticleAccessUtility = new ArticleAccessUtility($this->vkId);
            if ($ArticleAccessUtility->hasAccessToSourceType($targetFeedId, $sourceFeedType)) {

                $articleStatuses = $ArticleAccessUtility->getArticleStatusesForTargetFeed($targetFeedId);

                // фильтр по статусам - только для авторских постов
                // если мы запросили определенный статус и он входит в список разрешенных, то берем только его
                $reqArticleStatus = $this->getArticleStatus();
                Logger::Debug('Get article list with article status ' . $reqArticleStatus);
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
            // TODO узнать - только проверить доступ?
            if (!$TargetFeedAccessUtility->hasAccessToTargetFeed($targetFeedId)) {
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
     * Функция, расширяющая условия выборки
     * @return mixed
     */
    protected function processRequestCustom(){
        //pass
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
    }

    /**
     *
     */
    protected function setData()
    {
        Response::setArray('articles', $this->articles);
        Response::setArray('articleRecords', $this->articleRecords);
        Response::setInteger('articlesCount', $this->articlesCount);
        Response::setBoolean('hasMore', $this->hasMore);
        Response::setArray('authors', $this->authors);
        Response::setArray('commentsData', $this->commentsData);
    }
}
