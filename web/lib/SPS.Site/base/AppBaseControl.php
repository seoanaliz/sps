<?php
/**
 * Created by JetBrains PhpStorm.
 * User: x100up
 * Date: 16.01.13
 * Time: 14:28
 * To change this template use File | Settings | File Templates.
 */
class AppBaseControl extends BaseControl {
    public function __construct(){
        $this->vkId = Session::getInteger('authorId');
    }
}
