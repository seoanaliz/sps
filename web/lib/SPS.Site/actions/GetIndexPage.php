<?php
Package::Load('SPS.Site/base');
include __DIR__ . '/controls/GetSourceFeedsListControl.php';
include __DIR__ . '/controls/GetArticlesQueueTimelineControl.php';

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
                $currentTargetFeedId = 0;
            }
        }

        $availableSourceTypes = $gridTypes = array();
        if ( $currentTargetFeedId && isset( $targetFeeds[$currentTargetFeedId])) {
            $availableSourceTypes = $SourceAccessUtility->getAccessibleSourceTypes($targetFeeds[$currentTargetFeedId]);
            $gridTypes = $SourceAccessUtility->getAccessibleGridTypes($currentTargetFeedId);
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

        $sourceType = Cookie::getParameter('sourceType');
        if (!$sourceType) {
            $sourceType = reset($availableSourceTypes);
        }
        $sourceFeedsPrecache = GetSourceFeedsListControl::getData($this->vkId, $currentTargetFeedId, $sourceType);

        Response::setString('queueHtmlPrecache', $this->getArticlesQueueHtml($currentTargetFeedId));
        Response::setArray('sourceFeedsPrecache', $sourceFeedsPrecache); // используется во избежание дополнительного аякс-запроса при инициализации страницы
        Response::setArray('sourceFeeds', $sourceFeedsPrecache['sourceFeeds']); // используется для наполнения (правого) дропдауна targetFeed'ов
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

    protected function getArticlesQueueHtml($targetFeedId) {
        Request::setInteger('targetFeedId', $targetFeedId);

        $today = new DateTime('today');
        Request::setInteger('timestamp', $today->getTimestamp());

        $Control = new GetArticlesQueueTimelineControl();
        $Control->Execute();
        ob_start();
            $canEditQueue = Response::getBoolean('canEditQueue');
            $repostArticleRecords = Response::getArray('repostArticleRecords');
            $articleRecords = Response::getArray('articleRecords');
            $articlesQueue = Response::getArray('articlesQueue');
            $gridData = Response::getArray('gridData');
            include Template::GetCachedRealPath('tmpl://fe/elements/articles-queue-timeline.tmpl.php');
        $html = ob_get_clean();
        return $html;
    }
}

?>