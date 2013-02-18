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
        $this->isApp = true;
        parent::__construct();
        $this->vkId = Session::getInteger('authorId');
    }
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
        if (Request::getString('type') == 'my') {
            return SourceFeedUtility::My;
        }
        // показываем только авторские
        return SourceFeedUtility::Authors;
    }

    /**
     * Фича
     * @return string
     */
    protected function getMode(){
        if (Request::getString('type') == 'my') {
            return self::MODE_MY;
        }
        return parent::getMode();
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
        parent::processRequestCustom();

        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId){

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
            if ($role == UserFeed::ROLE_AUTHOR) {
                $this->userGroups = UserGroupFactory::GetForUserTargetFeed($targetFeedId, $this->vkId);
            } else {
                $this->userGroups = UserGroupFactory::GetForTargetFeed($targetFeedId);
            }
        }

        $type = $this->getSourceFeedType();

        if ($type == SourceFeedUtility::My){
            $this->options[BaseFactory::WithoutDisabled] = false;
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
            $this->authorCounter = AuthorEventUtility::GetAuthorCounter($this->getAuthor()->authorId);
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
        Response::setBoolean('showControls',
            (
                $this->search['page'] == 0
                && $this->getMode() != self::MODE_DEFERRED
                && !Request::getBoolean('articlesOnly')
            )
        );
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