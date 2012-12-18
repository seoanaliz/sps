<?php
Package::Load('SPS.Site/base');

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

        $type = Request::getString('type');
        if (empty($type) || empty(SourceFeedUtility::$Types[$type])) {
            $type = $ArticleAccessUtility->getDefaultType($targetFeedId);
        }

        if (!$ArticleAccessUtility->hasAccessToSourceType($targetFeedId, $type)) {
            // запросили недоступный тип, но мы тогда вернем дефолтный
            $type = $ArticleAccessUtility->getDefaultType($targetFeedId);
        }

        $result = array();
        if (!empty($targetFeedId)) {
            if ($type == SourceFeedUtility::Authors) {
                $authors = AuthorFactory::Get(
                    array(),
                    array(
                        BaseFactory::WithoutPages => true,
                        BaseFactory::CustomSql => ' AND "targetFeedIds" @> ARRAY[' . PgSqlConvert::ToInt($targetFeedId) . '] '
                    )
                );

                foreach ($authors as $author) {
                    $result[] = array(
                        'id' => $author->authorId,
                        'title' => $author->FullName()
                    );
                }
            } else {
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
                    $result[] = array(
                        'id' => $sourceFeed->sourceFeedId,
                        'title' => $sourceFeed->title
                    );
                }
            }
        } else {
            echo('Unknown source feed identifier');
        }


        echo ObjectHelper::ToJSON(array(
            'type' => $type,
            'sourceFeeds' => $result,
            'accessibleSourceTypes' => $ArticleAccessUtility->getAccessibleSourceTypes($targetFeedId),
            'accessibleGridTypes' => array_keys($ArticleAccessUtility->getAccessibleGridTypes($targetFeedId)),
            'canAddPlanCell' => $ArticleAccessUtility->canAddPlanCell($targetFeedId),
            'accessibleMyArticleStatuses' => $ArticleAccessUtility->getArticleStatusesForTargetFeed($targetFeedId)
        ));
    }
}

?>