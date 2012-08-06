<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 06.08.12
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */
class getStatusList
{
    public function execute()
    {
        error_reporting( 0 );



        echo ObjectHelper::ToJSON(array('response' => $dialog));

    }
}
