<?php
Package::Load('SPS.Site');

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

    protected function processPage(){
        parent::processPage('gaal_page');
    }




    private function processRequest()
    {
        $this->processPage();

        $author = Session::getObject('Author');

        $type = Request::getString('type');
        if (empty($type) || $type == 'null') {
            $type = Session::getString('gaal_type');
        }
        Session::setString('gaal_type', $type);

        //все авторские посты
        $this->search['sourceFeedId'] = SourceFeedUtility::FakeSourceAuthors;

        if (substr($type, 0, 1) == 'p') {
            $targetFeedId = substr($type, 1, strlen($type) - 1);
            $targetFeedIds = Session::getArray('targetFeedIds');
            if (empty($targetFeedIds) || !in_array($targetFeedId, $targetFeedIds)) {
                $type = 'my';
            } else {
                $type = 'targetFeed';
            }
        } else {
            $type = 'my';
        }

        Session::setInteger('gaal_targetFeedId', null);

        switch ($type) {
            case 'targetFeed':
                $this->search['targetFeedId'] = $targetFeedId;
                Session::setInteger('gaal_targetFeedId', $targetFeedId);
                break;
            case 'my':
            default:
                $this->search['authorId'] = $author->authorId;
                break;
        }

        $this->options[BaseFactory::WithoutDisabled] = false;

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
                if (empty($this->search['targetFeedId'])) {
                    $this->search['authorId'] = $author->authorId;
                }
            break;
        }

        $tabType = Request::getString('tabType');
        if (empty($tabType) || $tabType == 'null') {
            $tabType = Session::getString('gaal_tabType');
        }
        Session::setString('gaal_tabType', $tabType);
        Response::setString('tabType', $tabType);

        if (empty($this->search['authorId'])) {
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
    }

    protected function getObjects() {
        parent::getObjects();

        if (!empty($this->articles)) {
            $targetFeedIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('targetFeedId'));

            if (!empty($targetFeedIds)) {
                $this->targetFeeds = TargetFeedFactory::Get(array('_targetFeedId' => array_unique($targetFeedIds)));
            }

            if (!empty($this->search['authorId'])) {
                $this->authorEvents = AuthorEventFactory::Get(array('_articleId' => array_keys($this->articles)));
            }
            $this->commentsData = CommentUtility::GetLastComments(array_keys($this->articles), true, $this->authorEvents);
        }

        if (!empty($this->search['authorId'])) {
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

        Response::setArray('targetFeeds', $this->targetFeeds);

        //обновляем дату, когда пользователь последний раз смотрел паблик
        if (!empty($this->search['targetFeedId'])) {
            $author = Session::getObject('Author');
            AuthorFeedViewUtility::UpdateLastView($author->authorId, $this->search['targetFeedId']);
        }
    }
}

?>