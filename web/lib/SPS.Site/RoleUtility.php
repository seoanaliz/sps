<?php
/**
 * User: x100up
 * Date: 26.11.12 23:06
 * In Code We Trust
 */
class RoleUtility
{
    private $FeedRulesByRole = array();
    private $FeedRulesByFeed = array();

    public function __construct() {
        $this->loadRules();
    }


    public function loadRules() {
        $userId = AuthVkontakte::IsAuth();
        if ($userId) {
            $UserFeeds = UserFeedFactory::Get(array('vkId' => (int)$userId));
            $this->FeedRulesByRole = array();
            foreach ($UserFeeds as $UserFeed) {
                /** @var $UserFeed UserFeed */
                if (!isset($this->FeedRulesByRole[$UserFeed->role])) {
                    $this->FeedRulesByRole[$UserFeed->role] = array();
                }
                $this->FeedRulesByRole[$UserFeed->role][] = $UserFeed->targetFeedId;

                if (!isset($this->FeedRulesByFeed[$UserFeed->targetFeedId])) {
                    $this->FeedRulesByFeed[$UserFeed->targetFeedId] = array();
                }
                $this->FeedRulesByFeed[$UserFeed->targetFeedId][] = $UserFeed->role;
            }
        }
    }

    /**
     * @param $sourceType - тип ресурса
     */
    public function hasAccessToSourceType($targetFeedId, $sourceType){
        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
            if (in_array(UserFeed::ROLE_EDITOR, $this->FeedRulesByFeed[$targetFeedId]) && $sourceType == SourceFeedUtility::Ads) {
                return false;
            }
            return true;
        }
        #return false;
        return true;
    }

    public function getAccessibleSourceTypes($targetFeedId){
        $accessibleSourceTypes = array();
        foreach (SourceFeedUtility::$Types as $sourceType => $sourceTypeTitle) {
            if ($this->hasAccessToSourceType($targetFeedId, $sourceType)) {
                $accessibleSourceTypes[] = $sourceType;
            }
        }
        return $accessibleSourceTypes;
    }

    public function getAccessibleGridTypes($targetFeedId){
        $accessibleGridTypes = array();
        foreach (GridLineUtility::$Types as $gridType => $sourceTypeTitle) {
            if ($this->hasAccessToGridType($targetFeedId, $gridType)) {
                $accessibleGridTypes[] = $gridType;
            }
        }
        return $accessibleGridTypes;
    }

    public function hasAccessToGridType($targetFeedId, $gridType){

        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
            if (in_array(UserFeed::ROLE_EDITOR, $this->FeedRulesByFeed[$targetFeedId]) && $gridType == GridLineUtility::TYPE_ADS) {

                return false;
            }
            return true;
        }
        #return false;
        return true;
    }



    public function canAddPlanCell($targetFeedId) {
        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
            if (in_array(UserFeed::ROLE_EDITOR, $this->FeedRulesByFeed[$targetFeedId])) {
                return false;
            }
            return true;
        }
        #return false;
        return true;
    }
}
