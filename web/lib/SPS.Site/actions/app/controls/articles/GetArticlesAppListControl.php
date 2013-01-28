<?php
/**
 * Конторллер списка постов для VK приложения
 * GetArticlesAppListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
final class GetArticlesAppListControl extends BaseGetArticlesListControl {

    public function __construct(){
        parent::__construct();
        $this->vkId = Session::getInteger('authorId');
    }

    const MODE_MY = 'my';

    const MODE_ALL = 'all';

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

    private $userGroups = array();


    protected function getSourceFeedType(){
        // показываем только авторские
        return SourceFeedUtility::Authors;
    }

    protected function getMode(){
        // если есть лента - показываем для нее
        // безопасность проверена в parent::processRequest
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId) {
            return self::MODE_ALL;
        }

        return self::MODE_MY;
    }

    /**
     * Возвращает идентификатор запрошеной ленты
     */
    protected function getTargetFeedId(){
        $mode = Request::getString('type');
        if (substr($mode, 0, 1) == 'p') {
            return substr($mode, 1);
        }
        return null;
    }


    protected function processRequestCustom(){
        $author = $this->getAuthor();

        $mode = $this->getMode();
        if ($mode == self::MODE_MY) {
            $this->search['authorId'] = $author->authorId;
        } else {
            // получаем доступные ленты
            $targetFeedIds = $this->ArticleAccessUtility->getAllTargetFeedIds();
            $targetFeedId = $this->getTargetFeedId();
            if (!in_array($targetFeedId, $targetFeedIds)) {
                echo ObjectHelper::ToJSON(array('success' => false));
                return;
            }
        }

        // сортировка
        $filter = Request::getString('filter');
        switch ($filter) {
            case 'best':
                $this->options[BaseFactory::OrderBy] = ' "rate" DESC, "createdAt" DESC, "articleId" DESC ';
                break;
        }

        $tabType = Request::getString('tabType');
        Response::setString('tabType', $tabType);

        switch ($tabType) {
            case 'queued':
                $this->options[BaseFactory::OrderBy] = ' "queuedAt" DESC, "articleId" DESC ';
                $this->options[BaseFactory::CustomSql] = ' AND "queuedAt" IS NOT NULL ';
                break;
            case 'sent':
                $this->options[BaseFactory::OrderBy] = ' "sentAt" DESC, "articleId" DESC ';
                $this->options[BaseFactory::CustomSql] = ' AND "sentAt" IS NOT NULL ';
                break;
        }

        $this->options[BaseFactory::WithoutDisabled] = false;

        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId){
            //$TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            //$role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
            $this->userGroups = UserGroupFactory::GetForUserTargetFeed($this->getTargetFeedId(), $this->vkId);
        }
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

        $mode = $this->getMode();

        if (!empty($this->articles)) {
            // получаем ленты
            $targetFeedIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('targetFeedId'));
            if (!empty($targetFeedIds)) {
                $this->targetFeeds = TargetFeedFactory::Get(array('_targetFeedId' => array_unique($targetFeedIds)));
            }

            // получем события
            if ($mode == self::MODE_MY) {
                $this->authorEvents = AuthorEventFactory::Get(array('_articleId' => array_keys($this->articles)));
            }
        }

        if ($mode == self::MODE_MY) {
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
        Response::setArray('userGroups', $this->userGroups);
        Response::setBoolean('showControls', $this->search['page'] == 0 && (Request::getString('tabType') == 'null'));
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
            $author = $this->getAuthor();
            AuthorFeedViewUtility::UpdateLastView($author->authorId, $this->search['targetFeedId']);
        }
    }
}

?>