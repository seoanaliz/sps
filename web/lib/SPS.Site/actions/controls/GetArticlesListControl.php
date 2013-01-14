<?php
/**
 * GetArticlesListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticlesListControl extends BaseControl {

    /**
     * @var Article[]
     */
    private $articles = array();

    /**
     * @var ArticleRecord[]
     */
    private $articleRecords = array();

    /**
     * @var TargetFeed[]
     */
    private $targetFeeds = array();

    /**
     * @var SourceFeed[]
     */
    private $sourceFeeds = array();

    /**
     * @var Author[]
     */
    private $authors = array();

    /**
     * @var array
     */
    private $commentsData = array();

    /**
     * @var int
     */
    private $pageSize = 20;

    /**
     * @var int
     */
    private $articlesCount = 0;

    /**
     * @var bool
     */
    private $hasMore = false;

    /**
     * @var array
     */
    private $search = array();

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var string
     */
    private $articleLinkPrefix = 'http://vk.com/wall-';

    private function processRequest() {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        $sourceFeedIds  = Request::getArray('sourceFeedIds');
        $sourceFeedIds  = !empty($sourceFeedIds) ? $sourceFeedIds : array();
        $from           = Request::getInteger( 'from' );
        $to             = Request::getInteger( 'to' );
        $sortType       = Request::getString( 'sortType' );
        $type           = Request::getString( 'type' );

        $page = Session::getInteger( 'page' );
        $page = ($page < 0) ? 0 : $page;
        if (Request::getBoolean( 'clean' )) {
            $page = 0;
        }

        $this->search = array(
            '_sourceFeedId' => $sourceFeedIds,
            'pageSize' => $this->pageSize + 1,
            'page' => $page,
        );

        if ($from !== null) {
            $this->search['rateGE'] = $from;
        }
        if ($to !== null && $to < 100) {
            $this->search['rateLE'] = $to;
        }
        if ($sortType == 'old') {
            $this->options[BaseFactory::OrderBy] = ' "createdAt" ASC, "articleId" ASC ';
        } else if ($sortType == 'best') {
            $this->options[BaseFactory::OrderBy] = ' "rate" DESC, "createdAt" DESC, "articleId" DESC ';
        }

        //не авторские посты
        if (empty($this->search['_sourceFeedId']) && ($type != SourceFeedUtility::Authors) && ($type != SourceFeedUtility::Topface)) {
            $this->search['_sourceFeedId'] = array(-999 => -999);
            return;
        }

        //авторские посты
        if ($type == SourceFeedUtility::Authors) {
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            if (!$TargetFeedAccessUtility->hasAccessToTargetFeed($targetFeedId)) {
                $this->search['targetFeedId'] = -999;
            }

            $this->search['rateGE'] = null;
            $this->search['rateLE'] = null;
            $this->search['_sourceFeedId'] = array(SourceFeedUtility::FakeSourceAuthors => SourceFeedUtility::FakeSourceAuthors);
            $this->search['targetFeedId'] = $targetFeedId;

            //фильтр источников выступает как фильтр авторов
            if (!empty($sourceFeedIds)) {
                $this->search['_authorId'] = $sourceFeedIds;
            } else {
                $this->search['_authorId'] = array(-1 => -1);
            }
        }

        if ($type == SourceFeedUtility::Topface) {
            $targetFeedId = Request::getInteger( 'targetFeedId' );
            if (!$TargetFeedAccessUtility->hasAccessToTargetFeed($targetFeedId)) {
                $this->search['targetFeedId'] = -999;
            }

            $this->search['rateGE'] = null;
            $this->search['rateLE'] = null;
            $this->search['_sourceFeedId'] = array(SourceFeedUtility::FakeSourceTopface => SourceFeedUtility::FakeSourceTopface);
            $this->search['targetFeedId'] = $targetFeedId;
        }

        if ($type == SourceFeedUtility::Albums) {
            $this->articleLinkPrefix = 'http://vk.com/photo';
        }
    }

    private function getObjects() {
        $this->sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $this->search['_sourceFeedId']));

        $this->articles = ArticleFactory::Get($this->search, $this->options);
        $this->articlesCount = ArticleFactory::Count($this->search, $this->options + array(BaseFactory::WithoutPages => true));

        $this->hasMore = (count($this->articles) > $this->pageSize);
        $this->articles = array_slice($this->articles, 0, $this->pageSize, true);

        //load articles data
        if (!empty($this->articles)) {
            $this->articleRecords = ArticleRecordFactory::Get(
                array('_articleId' => array_keys($this->articles))
            );
        }
        if (!empty($this->articleRecords)) {
            $this->articleRecords = BaseFactoryPrepare::Collapse($this->articleRecords, 'articleId', false);
        }

        if ($this->hasMore) {
            Session::setInteger('page', $this->search['page'] + 1);
        }

        //get articles target feeds with info and authors
        if (!empty($this->articles)) {
            $authorIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('authorId'));
            if (!empty($authorIds)) {
                $this->authors = AuthorFactory::Get(
                    array('_authorId' => array_unique($authorIds)),
                    array(BaseFactory::WithoutPages => true)
                );
            }

            $this->commentsData = CommentUtility::GetLastComments(array_keys($this->articles));
        }
    }

    private function setData() {
        Response::setArray( 'articles', $this->articles );
        Response::setArray( 'articleRecords', $this->articleRecords );
        Response::setInteger( 'articlesCount', $this->articlesCount );
        Response::setBoolean( 'hasMore', $this->hasMore );
        Response::setArray( 'authors', $this->authors );
        Response::setArray( 'targetFeeds', $this->targetFeeds );
        Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($this->targetFeeds, 'targetFeedId') );
        Response::setArray( 'sourceFeeds', $this->sourceFeeds );
        Response::setArray( 'sourceInfo', SourceFeedUtility::GetInfo($this->sourceFeeds) );
        Response::setArray( 'commentsData', $this->commentsData );
        Response::setString('articleLinkPrefix', $this->articleLinkPrefix);
    }

    /**
     * Entry Point
     */
    public function Execute() {
        $this->processRequest();
        $this->getObjects();
        $this->setData();
    }
}
?>