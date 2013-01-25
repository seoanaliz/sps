<?php
/**
 * User: x100up
 * Date: 13.12.12 0:08
 * In Code We Trust
 */
class ArticleAccessUtility extends TargetFeedAccessUtility {

    /**
     * Возвращает возможные статусы постов для ленты
     * @param $targetFeedId
     * @return Array
     */
    public function getArticleStatusesForTargetFeed($targetFeedId){
        $role = $this->getRoleForTargetFeed($targetFeedId);
        if ($role == UserFeed::ROLE_AUTHOR) {
            // автор видит все записи
            return array(Article::STATUS_REVIEW, Article::STATUS_REJECT, Article::STATUS_APPROVED);
        } elseif ($role != UserFeed::ROLE_AUTHOR) {
            // редактор - одобренные и на рассмотрении
            return array(Article::STATUS_REVIEW, Article::STATUS_REJECT, Article::STATUS_APPROVED);
        }
        // одобренные записи видят все пользователи
        return array(Article::STATUS_APPROVED);
    }
}
