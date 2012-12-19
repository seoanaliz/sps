<?php
/**
 * User: x100up
 * Date: 15.12.12 16:55
 * In Code We Trust
 */

/**
 * Контролирует доступ к лентам отправки
 */
class TargetFeedAccessUtility extends RoleAccessUtility {

    /**
     * Возвращает роль
     * @param $targetFeedId
     * @return null|int
     */
    public function getRoleForTargetFeed($targetFeedId){
        if (isset(self::$FeedRulesByFeed[$targetFeedId])) {
            return self::$FeedRulesByFeed[$targetFeedId];
        }
        return null;
    }

    /**
     * Есть ли какой-нибудь доступ для ленты $targetFeed
     * @param $targetFeedId
     * @return bool
     */
    public function hasAccessToTargetFeed($targetFeedId){
        return in_array($targetFeedId, $this->getAllTargetFeedIds());
    }


    /**
     * TODO переделать
     * @param $targetFeedId
     * @return bool
     */
    public function canSaveGridLine($targetFeedId) {
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * Модет ли добавить автора
     * @param $targetFeedId
     * @return bool
     */
    public function canAddAuthor($targetFeedId){
        $role = $this->getRoleForTargetFeed($targetFeedId);
        if (!is_null($role)) {
            return $role != UserFeed::ROLE_AUTHOR;
        }
        return false;
    }

    /**
     * Может ли смотреть комменты у постов
     * @param $targetFeedId
     * @return bool
     */
    public function canShowArticleComments($targetFeedId){
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * @param $targetFeedId
     * @return bool
     */
    public function canSaveArticleComment($targetFeedId){
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * Может ли посмотреть список авторов
     * @param $targetFeedId
     * @return bool
     */
    public function canShowAuthorList($targetFeedId){
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * Может ли удалить автора
     * @param $targetFeedId
     * @return bool
     */
    public function canDeleteAuthor($targetFeedId){
        $role = $this->getRoleForTargetFeed($targetFeedId);
        if (!is_null($role)) {
            return $role != UserFeed::ROLE_AUTHOR;
        }
        return false;
    }

    /**
     * Может ли планировать удаление поста
     * @param $targetFeedId
     * @return bool
     */
    public function canCreatePlanDeletePost($targetFeedId){
        $role = $this->getRoleForTargetFeed($targetFeedId);
        if (!is_null($role)) {
            return $role != UserFeed::ROLE_AUTHOR;
        }
        return false;
    }

    /**
     * @param $targetFeedId
     * @return bool
     */
    public function canGetArticlesQueue($targetFeedId){
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * @param $targetFeedId
     * @return bool
     */
    public function canAddArticlesQueue($targetFeedId){
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * @param $targetFeedId
     * @return bool
     */
    public function canDeleteArticlesFromQueue($targetFeedId){
        return $this->hasAccessToTargetFeed($targetFeedId);
    }

    /**
     * Может ли добавить группу постов ?
     * @param $targetFeedId
     * @return bool
     */
    public function canAddUserGroup($targetFeedId){
        $role = $this->getRoleForTargetFeed($targetFeedId);
        if (!is_null($role)) {
            return $role != UserFeed::ROLE_AUTHOR;
        }
        return false;
    }
}
