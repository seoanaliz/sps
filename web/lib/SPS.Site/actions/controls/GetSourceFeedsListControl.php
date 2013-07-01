<?php
/**
 * GetSourceFeedsListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetSourceFeedsListControl extends BaseControl
{
    /**
     * Entry Point
     */
    public function Execute()
    {
        $ArticleAccessUtility = new ArticleAccessUtility($this->vkId);

        $targetFeedId = Request::getInteger('targetFeedId');

        $role = $ArticleAccessUtility->getRoleForTargetFeed($targetFeedId);

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
                    $userGroups = UserGroupFactory::GetForTargetFeed($targetFeedId);
                } else {
                    $userGroups = UserGroupFactory::GetForUserTargetFeed($targetFeedId, $this->vkId);
                }

                $showUserGroups = array();
                foreach ($userGroups as $userGroup) {
                    /** @var $userGroup UserGroup */
                    $showUserGroups[] = $userGroup->toArray();
                }
                // FIXME Вообще в авторских источники не нужны, наверное нужно будет вырезать...
                $UserFeedByRole = UserFeedFactory::GetForTargetFeed($targetFeedId);
                $authorVkIds = array();
                if (isset($UserFeedByRole[UserFeed::ROLE_AUTHOR])){
                    $authorVkIds = array_keys($UserFeedByRole[UserFeed::ROLE_AUTHOR]);
                }

                $authors = array();
                if ($authorVkIds){
                    $authors = AuthorFactory::Get(
                        array(
                           'vkIdIn' => $authorVkIds,
                        ),
                        array(
                            BaseFactory::WithoutPages => true,
                        )
                    );
                }

                foreach ($authors as $author) {
                    $sourceFeedResult[] = array(
                        'id' => $author->authorId,
                        'title' => $author->FullName()
                    );
                }

            } else {

                $showSourceList =  ($type == SourceFeedUtility::Topface
                    || $type == SourceFeedUtility::Source
                    || $type == SourceFeedUtility::Ads
                    || $type == SourceFeedUtility::Albums);

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

                    if( $type == SourceFeedUtility::Source ) {
                        $sourceFeeds += SourceFeedFactory::Get(
                              array(
                                '_sourceFeedId' => $sourceIds,
                                'type' => SourceFeedUtility::Topface )
                            , array(BaseFactory::WithoutPages => true)
                        );
                    }
                }

                foreach ($sourceFeeds as $sourceFeed) {
                    $sourceFeedResult[] = array(
                        'id' => $sourceFeed->sourceFeedId,
                        'title' => $sourceFeed->title
                    );
                }
            }

        $authorsFilters = array();
        if ($role == UserFeed::ROLE_AUTHOR && $type == SourceFeedUtility::Authors) {
            $authorsFilters['all_my_filter'] = array();
        }

        if ($role != UserFeed::ROLE_AUTHOR && ($type == SourceFeedUtility::Authors || $type == SourceFeedUtility::Albums)) {
            $authorsFilters['article_status_filter'] = array();
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
            'authorsFilters' => $authorsFilters
        ));
    }
}

?>