<?php
Package::Load('SPS.Site/base');
/**
 * Конторллер списка постов для VK приложения
 * GetArticlesAppListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
final class GetArticlesAppListControl extends BaseGetArticlesListControl {
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
        return null;
    }

    /**
     * Возвращает массив источников
     * @return array
     */
    protected function getSourceFeedIds() {
        $sourceFeedIds = Request::getArray('sourceFeedIds');
        return !empty($sourceFeedIds) ? $sourceFeedIds : array();
    }


    protected function processRequestCustom(){

        unset($this->options['_sourceFeedId']);

        $author = $this->getAuthor();
        $RoleUtility = new RoleAccessUtility($author->vkId);
        // получаем доступные ленты
        $targetFeedIds = $RoleUtility->getTargetFeedIds(UserFeed::ROLE_AUTHOR);


        //все авторские посты
        $this->search['sourceFeedId'] = SourceFeedUtility::FakeSourceAuthors;

        // если есть лента - показываем для нее
        // безопасность проверена в parent::processRequest
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId) {
            $this->mode = 'targetFeed';
        }


        if ($this->mode == 'my') {
            $this->search['authorId'] = $author->authorId;
        }

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
            $author = $this->getAuthor();
            AuthorFeedViewUtility::UpdateLastView($author->authorId, $this->search['targetFeedId']);
        }
    }
}

?>