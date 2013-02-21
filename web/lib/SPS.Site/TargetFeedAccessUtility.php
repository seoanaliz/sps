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
     * Круче чем автор
     * @param $targetFeedId
     * @return bool
     */
    protected function moreThenAuthor($targetFeedId){
        if ($targetFeedId == null) return false;
        $role = $this->getRoleForTargetFeed($targetFeedId);
        if (!is_null($role)) {
            return $role != UserFeed::ROLE_AUTHOR;
        }
        return false;
    }


    /**
     * TODO переделать
     * @param $targetFeedId
     * @return bool
     */
    public function canSaveGridLine($targetFeedId)    {
        return $this->moreThenAuthor($targetFeedId);
    }

    /**
     * Модет ли добавить автора
     * @param $targetFeedId
     * @return bool
     */
    public function canAddAuthor($targetFeedId){
        return $this->moreThenAuthor($targetFeedId);
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
        return $this->moreThenAuthor($targetFeedId);
    }

    /**
     * Может ли удалить автора
     * @param $targetFeedId
     * @return bool
     */
    public function canDeleteAuthor($targetFeedId){
        return $this->moreThenAuthor($targetFeedId);
    }

    /**
     * Может ли планировать удаление поста
     * @param $targetFeedId
     * @return bool
     */
    public function canCreatePlanDeletePost($targetFeedId){
        return $this->moreThenAuthor($targetFeedId);
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
        return $this->moreThenAuthor($targetFeedId);
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
        return $this->moreThenAuthor($targetFeedId);
    }
}
