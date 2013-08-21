<?php
/**
 * GetSourceFeedsListControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetSourceFeedsListControl extends BaseControl
{
    public function Execute() {
        echo ObjectHelper::ToJSON(
            self::getData($this->vkId, Request::getInteger('targetFeedId'), Request::getString('type'))
        );
    }

    /**
     * @return array
     */
    static public function getData($userVkId, $targetFeedId, $type) {
        $ArticleAccessUtility = new ArticleAccessUtility($userVkId);

        $targetFeed = TargetFeedFactory::GetById($targetFeedId);
        $accessibleSourceTypes = $ArticleAccessUtility->getAccessibleSourceTypes($targetFeed);

        $role = $ArticleAccessUtility->getRoleForTargetFeed($targetFeedId);

        if ( !isset(SourceFeedUtility::$Types[$type]) || !in_array($type, $accessibleSourceTypes)) {
            $type = reset( $accessibleSourceTypes );
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
                    $userGroups = UserGroupFactory::GetForUserTargetFeed($targetFeedId, $userVkId);
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

                $SourceAccessUtility = new SourceAccessUtility($userVkId);

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

        return array(
            'type' => $type,
            'sourceFeeds' => $sourceFeedResult,
            'accessibleSourceTypes' => $accessibleSourceTypes,
            'accessibleGridTypes' => array_keys($ArticleAccessUtility->getAccessibleGridTypes($targetFeedId)),
            'canAddPlanCell' => $ArticleAccessUtility->canAddPlanCell($targetFeedId),
            'accessibleMyArticleStatuses' => $ArticleAccessUtility->getArticleStatusesForTargetFeed($targetFeedId),
            'showArticleStatusFilter' => $showArticleStatusFilter,
            'showSourceList' => $showSourceList,
            'showUserGroups' => $showUserGroups,
            'authorsFilters' => $authorsFilters
        );
    }
}

?>