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

    public function __construct($vkId = null) {
        $this->loadRules($vkId);
    }

    /**
     * Загружает права пользователя
     * @param null $vkId
     */
    public function loadRules($vkId = null) {
        if (is_null($vkId)){
            $vkId = AuthVkontakte::IsAuth();
        }
        if ($vkId) {
            $UserFeeds = UserFeedFactory::Get(array('vkId' => (int)$vkId));
            $this->FeedRulesByRole = array();
            foreach ($UserFeeds as $UserFeed) {
                /** @var $UserFeed UserFeed */
                if (!isset($this->FeedRulesByRole[$UserFeed->role])) {
                    $this->FeedRulesByRole[$UserFeed->role] = array();
                }
                $this->FeedRulesByRole[$UserFeed->role][] = $UserFeed->targetFeedId;
                $this->FeedRulesByFeed[$UserFeed->targetFeedId] = $UserFeed->role;
            }
        }
    }

    public function getTargetFeedIds($role = null){
        if ($role){
            if (isset($this->FeedRulesByRole[$role]))  {
                return $this->FeedRulesByRole[$role];
            }
            return array();
        } else {
            return $this->FeedRulesByRole;
        }
    }

    /**
     * @param $targetFeedId - ид ленты
     * @param $sourceType - тип ресурса
     */
    public function hasAccessToSourceType($targetFeedId, $sourceType){
        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
            switch ($sourceType) {
                case SourceFeedUtility::Ads:
                    return !in_array($this->FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_EDITOR, UserFeed::ROLE_AUTHOR));
                break;

                case SourceFeedUtility::Albums:
                    return !in_array($this->FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
                break;

                case SourceFeedUtility::Source:
                    return !in_array($this->FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
                break;

                case SourceFeedUtility::Topface:
                    return !in_array($this->FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
                break;
            }
            return true;
        }
        return true;
    }

    /**
     * Возвращает роль
     * @param $targetFeedId
     */
    public function getRoleForTargetFeed($targetFeedId){
        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
            return $this->FeedRulesByFeed[$targetFeedId];
        }
        return null;
    }

    public function getAccessibleSourceTypes($targetFeedId){
        $accessibleSourceTypes = array();
        foreach (SourceFeedUtility::$Types as $sourceType => $sourceTypeTitle) {
            if ($this->hasAccessToSourceType($targetFeedId, $sourceType)) {
                $accessibleSourceTypes[$sourceType] = $sourceTypeTitle;
            }
        }
        return $accessibleSourceTypes;
    }

    public function getAccessibleGridTypes($targetFeedId){
        $accessibleGridTypes = array();
        foreach (GridLineUtility::$TitleTypes as $gridType => $gridTypeTitle) {
            if ($this->hasAccessToGridType($targetFeedId, $gridType)) {
                $accessibleGridTypes[$gridType] = $gridTypeTitle;
            }
        }
        return $accessibleGridTypes;
    }

    public function hasAccessToGridType($targetFeedId, $gridType){

        if (isset($this->FeedRulesByFeed[$targetFeedId])) {

            switch ($gridType)    {
                case GridLineUtility::TYPE_ADS:
                    return !in_array($this->FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_EDITOR, UserFeed::ROLE_AUTHOR));
                break;

                case GridLineUtility::TYPE_CONTENT:
                    return !in_array($this->FeedRulesByFeed[$targetFeedId], array());
                break;
            }

            return true;
        }
        #return false;
        return true;
    }



    public function canAddPlanCell($targetFeedId) {
        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
                return !in_array($this->FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
            return true;
        }
        #return false;
        return true;
    }

    public function getDefaultType($targetFeedId){
        if (isset($this->FeedRulesByFeed[$targetFeedId])) {
            switch ($this->FeedRulesByFeed[$targetFeedId]) {
               case UserFeed::ROLE_AUTHOR:
                    return SourceFeedUtility::Authors;
               break;
               default:
                return SourceFeedUtility::Source;
            }
        }
    }
}
