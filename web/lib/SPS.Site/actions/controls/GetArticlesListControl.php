<?php
Package::Load('SPS.Site/base');

/**
 * Конторллер списка постов для Socialboard
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticlesListControl extends BaseGetArticlesListControl {

    const MODE_MY = 'my';

    const MODE_ALL = 'all';

    /**
     * @var string
     */
    private $articleLinkPrefix = 'http://vk.com/wall-';

    /**
     * @var SourceFeed[]
     */
    private $sourceFeeds = array();

    private $reviewArticleCount = 0;

    protected function getMode(){
        $mode = Request::getString('mode');
        if ($mode == self::MODE_MY) {
            return self::MODE_MY;
        }
        return self::MODE_ALL;
    }

    protected function getAuthorsForTargetFeed() {
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
        return $authorsIds;
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

        $mode = $this->getMode();
        $targetFeedId = $this->getTargetFeedId();
        if (!$targetFeedId) {
            return array('success' => false);
        }

        $role = $this->ArticleAccessUtility->getRoleForTargetFeed($targetFeedId);
        if (is_null($role)) {
            return array('success' => false);
        }


        if ($type == SourceFeedUtility::Authors) {
            unset($this->search['_sourceFeedId']);
            // #11115
            if ($role == UserFeed::ROLE_AUTHOR) {
                if ($mode == self::MODE_MY) {
                    $authorsIds = array($this->getAuthor()->authorId);
                } else {
                    $authorsIds = $this->getAuthorsForTargetFeed();
                    $this->options[BaseFactory::CustomSql] = ' AND "sentAt" IS NOT NULL ';
                }
            } else {
                $authorsIds = $this->getAuthorsForTargetFeed();
            }
            // фильтр источников выступает как фильтр авторов
            if (!empty($authorsIds)) {
                $this->search['_authorId'] = $authorsIds;
            } else {
                $this->search['_authorId'] = array(-1 => -1);
            }
        }

        $userGroupId = Request::getInteger('userGroupId');
        if ($userGroupId) {
            $UserUserGroups = UserUserGroupFactory::Get(array('userGroupId' => $userGroupId));
            $vkIds = array();
            foreach ($UserUserGroups as $UserUserGroup) {
                $vkIds[] = $UserUserGroup->vkId;
            }
            $authors = $authorsIds = array();
            if ($vkIds) {
                $authors = AuthorFactory::Get(
                    array('vkIdIn' => $vkIds),
                    array( BaseFactory::WithoutPages => true)
                );
            }

            foreach ($authors as $author){
                $authorsIds[] = $author->authorId;
            }
            if (!empty($authorsIds)) {
                $this->search['_authorId'] = $authorsIds;
            } else {
                $this->search['_authorId'] = array(-1 => -1);
            }

            // в группе ищем записи на рассмотрении
            $this->reviewArticleCount = ArticleFactory::Count(array('authorId' => $this->getAuthor()->authorId,
                'articleStatusIn' => array(Article::STATUS_REVIEW), 'userGroupId' => $userGroupId));
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
        Response::setBoolean('reviewArticleCount', $this->reviewArticleCount);
    }
}

?>