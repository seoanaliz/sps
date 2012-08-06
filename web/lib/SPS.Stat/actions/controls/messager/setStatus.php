<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 06.08.12
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */
class setStatus
{
    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $status_id      =   Request::getInteger( 'statId' );

        $status_id      =   $status_id ? $status_id : 0;

        if ( !$user_id || !$rec_id) {
            die(ERR_MISSING_PARAMS);
        }

        $dialog = StatUsers::add_user( $rec_id );

        if (! ($dialog['id'] = MesDialogs::addDialog($user_id, $rec_id, $status_id ) ) ) {
            echo  ObjectHelper::ToJSON(array('response' => false));
            die();
        }

        echo ObjectHelper::ToJSON(array('response' => $dialog));

    }
}
