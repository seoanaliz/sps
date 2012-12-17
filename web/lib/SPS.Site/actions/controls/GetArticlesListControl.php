<?php
Package::Load('SPS.Site/base');

/**
 * Конторллер списка постов для Socialboard
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticlesListControl extends BaseGetArticlesListControl {
    /**
     * @var string
     */
    private $articleLinkPrefix = 'http://vk.com/wall-';

    /**
     * @var SourceFeed[]
     */
    private $sourceFeeds = array();

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
     * Entry Point
     */
    public function Execute()
    {
        $this->processRequest();
        $this->getObjects();

        // подгружаем источники
        $this->sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $this->getSourceFeedIds()));

        $this->setData();

        $showApproveBlock = false;
        $targetFeedId = $this->getTargetFeedId();
        if ($targetFeedId) {
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $showApproveBlock = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId) == UserFeed::ROLE_EDITOR;

            $showApproveBlock = ($showApproveBlock && $this->getArticleStatus() == Article::STATUS_REVIEW);

        }


        Response::setString('articleLinkPrefix', $this->articleLinkPrefix);
        Response::setArray('sourceFeeds', $this->sourceFeeds);
        Response::setArray('sourceInfo', SourceFeedUtility::GetInfo($this->sourceFeeds));
        Response::setBoolean('showApproveBlock', $showApproveBlock);
    }
}

?>