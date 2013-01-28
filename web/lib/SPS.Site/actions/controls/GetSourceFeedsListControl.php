<?php
/**
 * GetSourceFeedsListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetSourceFeedsListControl extends BaseControl
{
    private $canApproveArticles = false;
    /**
     * Entry Point
     */
    public function Execute()
    {
        $ArticleAccessUtility = new ArticleAccessUtility($this->vkId);

        $targetFeedId = Request::getInteger('targetFeedId');

        $role = $ArticleAccessUtility->getRoleForTargetFeed($targetFeedId);

        $this->canApproveArticles = ($role != UserFeed::ROLE_AUTHOR);

        $type = Request::getString('type');
        if (empty($type) || empty(SourceFeedUtility::$Types[$type])) {
            $type = $ArticleAccessUtility->getDefaultType($targetFeedId);
        }

        if (!$ArticleAccessUtility->hasAccessToSourceType($targetFeedId, $type)) {
            // запросили недоступный тип, но мы тогда вернем дефолтный
            $type = $ArticleAccessUtility->getDefaultType($targetFeedId);
        }

        /**
         * Показывать фильтр по типам постов
         */
        $showArticleStatusFilter = false;

        /**
         * Показывать список источников
         */
        $showSourceList = false;

        /**
         *  Показывать группы юзеров
         */
        $showUserGroups = false;

        $sourceFeedResult = array();

        if ($type == SourceFeedUtility::My) {
            if ($role == UserFeed::ROLE_AUTHOR) {
                $showArticleStatusFilter = true;
            }
        } else
            if ($type == SourceFeedUtility::Authors) {

                if ($role != UserFeed::ROLE_AUTHOR) {
                    //$showArticleStatusFilter = true;

                    $userGroups = UserGroupFactory::GetForTargetFeed($targetFeedId);
                    $showUserGroups = array();

                    foreach ($userGroups as $userGroup) {
                        /** @var $userGroup UserGroup */
                        $showUserGroups[] = $userGroup->toArray();
                    }
                }

                $authors = AuthorFactory::Get(
                    array(),
                    array(
                        BaseFactory::WithoutPages => true,
                        BaseFactory::CustomSql => ' AND "targetFeedIds" @> ARRAY[' . PgSqlConvert::ToInt($targetFeedId) . '] '
                    )
                );

                foreach ($authors as $author) {
                    $sourceFeedResult[] = array(
                        'id' => $author->authorId,
                        'title' => $author->FullName()
                    );
                }
            } else {

                $showSourceList =  ($type == SourceFeedUtility::Topface || $type == SourceFeedUtility::Source || $type == SourceFeedUtility::Albums);

                $SourceAccessUtility = new SourceAccessUtility($this->vkId);

                $sourceIds = $SourceAccessUtility->getSourceIdsForTargetFeed($targetFeedId);
                $sourceFeeds = array();
                if ($sourceIds) {
                    $sourceFeeds = SourceFeedFactory::Get(
                        array(
                            '_sourceFeedId' => $sourceIds,
                            'type' => $type)
                        , array(BaseFactory::WithoutPages => true)
                    );
                }

                foreach ($sourceFeeds as $sourceFeed) {
                    $sourceFeedResult[] = array(
                        'id' => $sourceFeed->sourceFeedId,
                        'title' => $sourceFeed->title
                    );
                }
            }

        echo ObjectHelper::ToJSON(array(
            'type' => $type,
            'sourceFeeds' => $sourceFeedResult,
            'accessibleSourceTypes' => $ArticleAccessUtility->getAccessibleSourceTypes($targetFeedId),
            'accessibleGridTypes' => array_keys($ArticleAccessUtility->getAccessibleGridTypes($targetFeedId)),
            'canAddPlanCell' => $ArticleAccessUtility->canAddPlanCell($targetFeedId),
            'accessibleMyArticleStatuses' => $ArticleAccessUtility->getArticleStatusesForTargetFeed($targetFeedId),
            'showArticleStatusFilter' => $showArticleStatusFilter,
            'showSourceList' => $showSourceList,
            'showUserGroups' => $showUserGroups,
            'canApproveArticles' => $this->canApproveArticles,
        ));
    }
}

?>