<?php
Package::Load('SPS.Site/base');

/**
 * GetIndexPage Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetIndexPage extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $SourceAccessUtility = new SourceAccessUtility($this->vkId);

        /**
         * current values from settings
         */
        $currentTargetFeedId = SettingsUtility::GetTarget();
        if ($currentTargetFeedId) {
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            if (!$TargetFeedAccessUtility->hasAccessToTargetFeed($currentTargetFeedId)) {
                $currentTargetFeedId = null;
            }
        }

        /**
         * target feeds
         */
        $targetFeeds = array();

        $targetFeedIds = $SourceAccessUtility->getAllTargetFeedIds();
        if ($targetFeedIds){
            $targetFeeds = TargetFeedFactory::Get(array('_targetFeedId' => $targetFeedIds));
        }

        if (empty($currentTargetFeedId)) {
            //пытаемся получить источники для первого паблика
            if (!empty($targetFeeds)) {
                $currentTargetFeedId = current(array_keys($targetFeeds));
            } else {
                $currentTargetFeedId = -1;
            }
        }


        $availableSourceTypes = $gridTypes = array();
        if ($currentTargetFeedId) {
            $availableSourceTypes = $SourceAccessUtility->getAccessibleSourceTypes($currentTargetFeedId);
            $gridTypes = $SourceAccessUtility->getAccessibleGridTypes($currentTargetFeedId);
        }

        $sourceFeedIds = $SourceAccessUtility->getSourceIdsForTargetFeed($currentTargetFeedId);

        Logger::Debug('$sourceFeedIds = '.print_r($sourceFeedIds, true));
        $sourceFeeds = array();
        if ($sourceFeedIds) {
            $sourceFeeds = SourceFeedFactory::Get(
                array('_sourceFeedId' => $sourceFeedIds)
                ,array(BaseFactory::WithoutPages => true)
            );
        }

        $ArticleAccessUtility = new ArticleAccessUtility($this->vkId);

        // фильтры по статусам статей
        $availableArticleStatuses = array();
        if ($currentTargetFeedId) {
            $availableArticleStatuses = $ArticleAccessUtility->getArticleStatusesForTargetFeed($currentTargetFeedId);
        }
        $articleStatuses = Article::getStatuses();

        $isShowSourceList = true;
        if ($currentTargetFeedId) {
            $role = $ArticleAccessUtility->getRoleForTargetFeed($currentTargetFeedId);
            if ($role == UserFeed::ROLE_AUTHOR){
                $isShowSourceList = false;
            }
        }

        Response::setArray('sourceFeeds', $sourceFeeds);
        Response::setArray('targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId'));
        Response::setArray('targetFeeds', $targetFeeds);
        Response::setInteger('currentTargetFeedId', $currentTargetFeedId);
        Response::setParameter('currentDate', SettingsUtility::GetDate());
        Response::setParameter('SourceAccessUtility', $SourceAccessUtility);
        Response::setParameter('sourceTypes', SourceFeedUtility::$Types);
        Response::setParameter('availableSourceTypes', $availableSourceTypes);
        Response::setParameter('gridTypes', $gridTypes);
        Response::setParameter('availableArticleStatuses', $availableArticleStatuses);
        Response::setParameter('articleStatuses', $articleStatuses);
        Response::setParameter('isShowSourceList', $isShowSourceList);
    }
}

?>