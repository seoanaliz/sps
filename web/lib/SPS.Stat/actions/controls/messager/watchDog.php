<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 07.08.12
 * Time: 12:37
 * To change this template use File | Settings | File Templates.
 */
class watchDog
{

    public function Execute() {
        error_reporting( 0 );
        $userId     =   Request::getInteger( 'userId' );

    }
}
