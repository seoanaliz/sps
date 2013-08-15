<?php
Package::Load('SPS.Site/base');
include __DIR__ . '/controls/GetSourceFeedsListControl.php';
include __DIR__ . '/controls/GetArticlesQueueTimelineControl.php';
include __DIR__ . '/controls/GetArticlesListControl.php';

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

        $sourceArticlesPrecache = $this->getSourceArticlesPrecache($currentTargetFeedId, $sourceType, array_map(
            function ($elem) {return $elem['id'];}, $sourceFeedsPrecache['sourceFeeds']
        ));

        Response::setString('sourceArticlesPrecache', $sourceArticlesPrecache);
        Response::setString('articlesQueuePrecache', $this->getArticlesQueuePrecache($currentTargetFeedId));
        Response::setArray('sourceFeedsPrecache', $sourceFeedsPrecache); // во избежание дополнительного аякс-запроса при инициализации страницы
        Response::setArray('sourceFeeds', $sourceFeedsPrecache['sourceFeeds']); // (правый) дропдаун targetFeed'ов
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

    /**
     * HTML ленты статей-источников
     * @return string
     */
    protected function getSourceArticlesPrecache($targetFeedId, $sourceType, $availableSourceFeedIds) {
        Response::setString('sortType', 'new');
        Response::setInteger('page', 0);
        Response::setString('type', $sourceType);
        Response::setInteger('targetFeedId', $targetFeedId);

        $souceFeedsCookie = Cookie::getParameter('sourceFeedIds_source_' . $targetFeedId);
        $sourceFeedIds = $souceFeedsCookie ? explode('.', $souceFeedsCookie) : $availableSourceFeedIds;
        Response::setArray('sourceFeedIds', $sourceFeedIds);

        $range = Cookie::getParameter($sourceType . 'FeedRange' . $targetFeedId);
        $split = explode(':', $range);
        if (count($split) === 2) {
            list($from, $to) = $split;
        } else {
            $from = 50;
            $to = 100;
        }
        Response::setInteger('from', $from);
        Response::setInteger('to', $to);

        $Control = new GetArticlesListControl();
        $Control->Execute();
        extract(Response::getParameters()); // да-да, я знаю, капитан, что так делать не нужно
        ob_start();
            include Template::GetCachedRealPath('tmpl://fe/elements/articles-list.tmpl.php');
        $html = ob_get_clean();
        return $html;
    }
    
    /**
     * HTML ленты отправки
     * @return string
     */
    protected function getArticlesQueuePrecache($targetFeedId) {
        Request::setInteger('targetFeedId', $targetFeedId);

        $today = new DateTime('today');
        Request::setInteger('timestamp', $today->getTimestamp());

        $targetType = Cookie::getParameter('targetTypes' . $targetFeedId) ?: 'content';
        Request::setString('type', $targetType);

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