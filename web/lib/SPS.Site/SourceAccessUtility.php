<?php
/**
 * User: x100up
 * Date: 10.12.12 22:53
 * In Code We Trust
 */
class SourceAccessUtility extends RoleAccessUtility
{
    protected $sourceFeedIds = array();

    protected $sourceFeedIdsByTargetFeed = array();

    public function __construct($vkId) {
        parent::__construct($vkId);
        $this->load();
    }

    protected function load(){
        $this->sourceFeedIds = array();

        // получаем все строки!!!
        // TODO переделать на массив
        $sourceFeeds = SourceFeedFactory::Get(array(),
            array(BaseFactory::WithoutPages => true, BaseFactory::WithColumns => '"sourceFeedId", "targetFeedIds"')
        );

        $sourceFeedIds = array(-1, -2);
        $sourceFeedIdsByTargetFeed = array();

        if (!empty($sourceFeeds)) {
            foreach ($sourceFeeds as $sourceFeed) {
                $targetFeedIds = explode(',', $sourceFeed->targetFeedIds);
                if (!empty($targetFeedIds)) {
                    $sourceFeedIds[] = $sourceFeed->sourceFeedId;
                    foreach ($targetFeedIds as $targetFeedId){
                        if (!isset($sourceFeedIdsByTargetFeed[$targetFeedId])) {
                            $sourceFeedIdsByTargetFeed[$targetFeedId] = array(-1, -2);
                        }
                        $sourceFeedIdsByTargetFeed[$targetFeedId][] = $sourceFeed->sourceFeedId;
                    }
                }
            }
        }

        $this->sourceFeedIdsByTargetFeed = $sourceFeedIdsByTargetFeed;
        $this->sourceFeedIds = $sourceFeedIds;
    }

    /**
     * @param $targetFeedId - идентификатор ленты
     * @return array
     */
    public function getSourceIdsForTargetFeed($targetFeedId){
        if (isset($this->sourceFeedIdsByTargetFeed[$targetFeedId])) {
            return $this->sourceFeedIdsByTargetFeed[$targetFeedId];
        }
        return array();
    }

    /**
     * Любой доступ
     * @param $sourceFeedId
     * @return bool
     */
    public function hasAccessToSourceFeed($sourceFeedId) {
        return in_array($sourceFeedId, $this->sourceFeedIds);
    }
}
