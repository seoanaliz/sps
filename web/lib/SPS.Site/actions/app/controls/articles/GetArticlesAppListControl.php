<?php
include_once(dirname(__FILE__) . '/../../../controls/GetArticlesListControl.php');
/**
 * Конторллер списка постов для VK приложения
 * GetArticlesAppListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
final class GetArticlesAppListControl extends GetArticlesListControl {
    /**
     * @var AuthorEvent[]
     */
    private $authorEvents = array();

    /**
     * @var array
     */
    private $authorCounter = array();

    /**
     * Использовать фильтр по рейтингу
     * @var bool
     * @override
     */
    protected $userRateFilter = false;

    /**
     * по умолчанию - режим показа моих записей
     * @var string
     */
    private $mode = 'my';

    protected function processPage($sessionKey = 'gaal_page'){
        parent::processPage($sessionKey);
    }

    protected function getSourceFeedType(){
        // показываем только авторские
        return SourceFeedUtility::Authors;
    }

    /**
     * Возвращает идентификатор запрошеной ленты
     */
    protected function getTargetFeedId(){
        $mode = Request::getString('type');
        if (substr($mode, 0, 1) == 'p') {
            return substr($mode, 1);
        }
    }

    protected function processRequestCustom(){
        $author = Session::getObject('Author');
        $RoleUtility = new RoleAccessUtility($author->vkId);
        // получаем доступные ленты
        $targetFeedIds = $RoleUtility->getTargetFeedIds(UserFeed::ROLE_AUTHOR);

        //$mode = Request::getString('type');
        //if (empty($mode) || $mode == 'null') {
        //    $mode = Session::getString('gaal_type');
        //}
        //Session::setString('gaal_type', $mode);

        //все авторские посты
        $this->search['sourceFeedId'] = SourceFeedUtility::FakeSourceAuthors;

        // если есть лента - показываем для нее
        // безопасность проверена в parent::processRequest
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId) {
            $this->mode = 'targetFeed';
        }

        //Session::setInteger('gaal_targetFeedId', null);

        if ($this->mode == 'my') {
            $this->search['authorId'] = $author->authorId;
        }

        /*
        switch ($mode) {
            case 'targetFeed':
                $this->search['targetFeedId'] = $targetFeedId;
                Session::setInteger('gaal_targetFeedId', $targetFeedId);
                break;
            default:
                $this->search['authorId'] = $author->authorId;
                break;
        }  */

        // сортировка
        $filter = Request::getString('filter');
        switch ($filter) {
            case 'best':
                $this->options[BaseFactory::OrderBy] = ' "rate" DESC, "createdAt" DESC, "articleId" DESC ';
                break;
            case 'new':
                // дефолтная сортировка
                break;
            case 'my':
            default:
                //if (empty($this->search['targetFeedId'])) {
                //    $this->search['authorId'] = $author->authorId;
                //}
            break;
        }

        $tabType = Request::getString('tabType');
        if (empty($tabType) || $tabType == 'null') {
            $tabType = Session::getString('gaal_tabType');
        }
        Session::setString('gaal_tabType', $tabType);
        Response::setString('tabType', $tabType);

        if ($this->mode == 'my') {
            $tabType = 'all';
        }

        switch ($tabType) {
            case 'queued':
                $this->options[BaseFactory::OrderBy] = ' "queuedAt" DESC, "articleId" DESC ';
                $this->options[BaseFactory::CustomSql] = ' AND "queuedAt" IS NOT NULL ';
                break;
            case 'sent':
                $this->options[BaseFactory::OrderBy] = ' "sentAt" DESC, "articleId" DESC ';
                $this->options[BaseFactory::CustomSql] = ' AND "sentAt" IS NOT NULL ';
                break;
            case 'all':
            default:
                // дефолтная сортировка
            break;
        }

        $this->options[BaseFactory::WithoutDisabled] = false;
    }

    /**
     * Загрузка комментариев
     */
    protected function loadComments(){
        if ($this->articles)    {
            $this->commentsData = CommentUtility::GetLastComments(array_keys($this->articles), true, $this->authorEvents);
        }
    }

    /**
     * Получаем объекты
     */
    protected function getObjects() {
        // статьи, комменты и т.д.
        parent::getObjects();

        if (!empty($this->articles)) {
            // получаем ленты
            $targetFeedIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('targetFeedId'));
            if (!empty($targetFeedIds)) {
                $this->targetFeeds = TargetFeedFactory::Get(array('_targetFeedId' => array_unique($targetFeedIds)));
            }

            // получем события
            if ($this->mode == 'my') {
                $this->authorEvents = AuthorEventFactory::Get(array('_articleId' => array_keys($this->articles)));
            }
        }

        if ($this->mode == 'my') {
            $this->authorCounter = AuthorEventUtility::GetAuthorCounter($this->search['authorId']);
        }
    }

    protected function setData()
    {
        parent::setData();
        Response::setArray('targetFeeds', $this->targetFeeds);
        Response::setArray('targetInfo', SourceFeedUtility::GetInfo($this->targetFeeds, 'targetFeedId'));
        Response::setArray('authorEvents', $this->authorEvents);
        Response::setArray('__authorCounter', $this->authorCounter);
    }

    /**
     * Entry Point
     */
    public function Execute()
    {
        $this->processRequest();
        $this->getObjects();
        $this->setData();

        //обновляем дату, когда пользователь последний раз смотрел паблик
        if (!empty($this->search['targetFeedId'])) {
            $author = Session::getObject('Author');
            AuthorFeedViewUtility::UpdateLastView($author->authorId, $this->search['targetFeedId']);
        }
    }
}

?>