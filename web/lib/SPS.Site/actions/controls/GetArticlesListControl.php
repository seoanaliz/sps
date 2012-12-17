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


        if ($type == SourceFeedUtility::Authors) {
            //$this->search['_sourceFeedId'] = array(SourceFeedUtility::FakeSourceAuthors => SourceFeedUtility::FakeSourceAuthors);
            unset($this->search['_sourceFeedId']);
            // определяем источники
            //$sourceFeedIds = $this->getSourceFeedIds();

            // выираем авторов для этой ленты
            $authorsIds = array();
            $UserFeeds = UserFeedFactory::Get(array('targetFeedId' => $this->search['targetFeedId']));
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

            // фильтр источников выступает как фильтр авторов
            if (!empty($authorsIds)) {
                $this->search['_authorId'] = $authorsIds;
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