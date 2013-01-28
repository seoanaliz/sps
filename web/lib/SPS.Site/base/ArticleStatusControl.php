<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prog-31
 * Date: 11.12.12
 * Time: 17:00
 * To change this template use File | Settings | File Templates.
 */
abstract class ArticleStatusControl extends BaseControl {

    /**
     * Изменяем статус записи
     * @param $articleId
     * @param $newArticleStatus
     */
    public function changeArticleStatusTo($articleId, $newArticleStatus){
        // TODO Добавить секьюрити
        $Article = ArticleFactory::GetById($articleId);
        $Article->articleStatus = $newArticleStatus;
        ArticleFactory::UpdateByMask($Article, array('articleStatus'), array('articleId' => $Article->articleId));
    }
}
