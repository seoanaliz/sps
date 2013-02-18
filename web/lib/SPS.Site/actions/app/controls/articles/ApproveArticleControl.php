<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prog-31
 * Date: 11.12.12
 * Time: 16:58
 * To change this template use File | Settings | File Templates.
 */
Package::load('SPS.Site/base');

class ApproveArticleControl extends ArticleStatusControl {

    public function Execute() {
        $id = Request::getInteger('id');
        $this->changeArticleStatusTo($id, Article::STATUS_APPROVED);
    }
}
