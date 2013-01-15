<?php
/**
 * User: x100up
 * Date: 10.12.12 22:53
 * In Code We Trust
 */
class SourceAccessUtility
{
    /**
     *
     */
    public function __construct($vkId){
        $this->vkId = $vkId;
    }

    /**
     * @param $feedId - ижентификатор ленты
     */
    public function getSourceIdsForFeed($feedId){
        // получеам все строки!!! почему не использовать массив?
        $sourceFeeds = SourceFeedFactory::Get(array('containsFeedId' => $feedId),
            array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"sourceFeedId", "targetFeedIds"')
        );

        $sourceFeedIds = array();

        if (!empty($sourceFeeds)) {
            foreach ($sourceFeeds as $sourceFeed) {
                $targetFeedIds = explode(',', $sourceFeed->targetFeedIds);
                if (!empty($targetFeedIds) && in_array($feedId, $targetFeedIds)) {
                    $sourceFeedIds[] = $sourceFeed->sourceFeedId;
                }
            }
        }

        return $sourceFeedIds;
    }
}
