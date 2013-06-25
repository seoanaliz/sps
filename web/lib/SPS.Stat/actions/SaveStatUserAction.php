<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.05.13
 * Time: 13:29
 * To change this template use File | Settings | File Templates.
 */
class SaveStatUserAction extends BaseControl
{
    public function Execute()
    {
        $statUsers = StatUserFactory::Get();
        Response::setArray('statUsers', $statUsers);
    }
}
