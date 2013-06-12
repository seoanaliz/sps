<?php
/**
 * User: x100up
 * Date: 26.11.12 23:06
 * In Code We Trust
 */
class RoleAccessUtility
{
    protected static $FeedRulesByRole = array();
    protected static $FeedRulesByFeed = array();
    public static $TopFaceFeeds = array(22);

    private static $isLoaded = false;

    public function __construct($vkId) {
        if (!self::$isLoaded) {
            $this->loadRules($vkId);
        }
    }

    /**
     * Загружает права пользователя
     * @param null $vkId
     */
    private function loadRules($vkId) {
        if ($vkId) {
            $UserFeeds = UserFeedFactory::Get(array('vkId' => (int)$vkId));
            self::$FeedRulesByRole = array();
            foreach ($UserFeeds as $UserFeed) {
                /** @var $UserFeed UserFeed */
                if (!isset(self::$FeedRulesByRole[$UserFeed->role])) {
                    self::$FeedRulesByRole[$UserFeed->role] = array();
                }
                self::$FeedRulesByRole[$UserFeed->role][] = $UserFeed->targetFeedId;
                self::$FeedRulesByFeed[$UserFeed->targetFeedId] = $UserFeed->role;
            }

            self::$isLoaded = true;
        }
    }

    /**
     * Возвращает ленты отправки пользователя
     * Если указана роль - то масси лент
     * Если нет - то array(role => array(targetFeedId))
     * @param null $role
     * @return array
     */
    public function getTargetFeedIds($role = null){
        if ($role){
            if (isset(self::$FeedRulesByRole[$role]))  {
                return self::$FeedRulesByRole[$role];
            }
            return array();
        } else {
            return self::$FeedRulesByRole;
        }
    }

    /**
     * Возвращает все ленты, доступные пользователю
     * @return array
     */
    public function getAllTargetFeedIds(){
        $targetFeedIds = array();
        $targetFeedIdsByRole = $this->getTargetFeedIds();
        foreach ($targetFeedIdsByRole as $roleTargetFeedIds){
            $targetFeedIds = array_merge($targetFeedIds, $roleTargetFeedIds);
        }
        return $targetFeedIds;
    }


    /**
     * @param $targetFeedId - ид ленты
     * @param $sourceType - тип ресурса
     * @return bool
     */
    public function hasAccessToSourceType($targetFeedId, $sourceType){
        if (isset(self::$FeedRulesByFeed[$targetFeedId])) {
            switch ($sourceType) {
                case SourceFeedUtility::Ads:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_EDITOR, UserFeed::ROLE_AUTHOR));
                break;

                case SourceFeedUtility::Albums:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
                break;

                case SourceFeedUtility::Source:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
                break;

                case SourceFeedUtility::Topface:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR)) && in_array($targetFeedId, self::$TopFaceFeeds);
                break;

                case SourceFeedUtility::AuthorsList:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
                break;
            }
            return true;
        }
        return true;    
    }

    /**
     * Созвращает массив источников, доступных для ленты (с учетом настроек ленты)
     * @param $targetFeedId
     * @return array
     */
    public function getAccessibleSourceTypes($targetFeedId) {
        $targetFeed = TargetFeedFactory::GetById( $targetFeedId );
        $accessibleSourceTypes = array();
        foreach (SourceFeedUtility::$Types as $sourceType => $sourceTypeTitle) {
            if (    $this->hasAccessToSourceType($targetFeed->targetFeedId, $sourceType)
                &&  isset( $targetFeed->params['showTabs'][$sourceType] )
                &&  $targetFeed->params['showTabs'][$sourceType] == 'on' ) {

                    $accessibleSourceTypes[$sourceType] = $sourceTypeTitle;
            }
        }
        return array_keys($accessibleSourceTypes);
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

        if (isset(self::$FeedRulesByFeed[$targetFeedId])) {

            switch ($gridType)    {
                case GridLineUtility::TYPE_ADS:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_EDITOR, UserFeed::ROLE_AUTHOR));
                break;

                case GridLineUtility::TYPE_CONTENT:
                    return !in_array(self::$FeedRulesByFeed[$targetFeedId], array());
                break;
            }

            return true;
        }
        #return false;
        return true;
    }



    public function canAddPlanCell($targetFeedId) {
        if (isset(self::$FeedRulesByFeed[$targetFeedId])) {
                return !in_array(self::$FeedRulesByFeed[$targetFeedId], array(UserFeed::ROLE_AUTHOR));
        }
        return true;
    }

    public function getDefaultType($targetFeedId){
        if (isset(self::$FeedRulesByFeed[$targetFeedId])) {
            switch (self::$FeedRulesByFeed[$targetFeedId]) {
               case UserFeed::ROLE_AUTHOR:
                    return SourceFeedUtility::Authors;
               break;
               default:
                return SourceFeedUtility::Source;
            }
        }
    }
}
