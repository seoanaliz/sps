<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 08.08.12
 * Time: 12:16
 * To change this template use File | Settings | File Templates.
 */
class saveAt
{
    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $access_token   =   Request::getString(  'access_token' );

        if ( !$access_token || !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $user = StatUsers::is_our_user( $user_id );
        if ( !$user)
            $user = StatUsers::add_user( $user_id );

//        StatUsers::set_access_token( $user_id, $access_token );
//        MesDialogs::get_all_dialogs( $user_id );
        MesDialogs::check_friend_requests( $user_id );

        die( ObjectHelper::ToJSON( array( 'response' => $user )));
    }
}
