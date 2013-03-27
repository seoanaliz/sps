<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prog-31
 * Date: 11.12.12
 * Time: 17:11
 * To change this template use File | Settings | File Templates.
 */

class RejectArticleControl extends ArticleStatusControl {

    public function Execute() {
        $id = Request::getInteger('id');
        $this->changeArticleStatusTo($id, Article::STATUS_REJECT);
    }
}
