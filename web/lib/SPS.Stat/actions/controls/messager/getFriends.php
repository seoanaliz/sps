<?php


class getFriends
{
    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );

        if ( !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $friends = MesDialogs::get_friend_requests( $user_id );
        $id_list = '';
        foreach( $friends as $friend) {
            $id_list .= $friend->uid;
        }

        $res = StatUsers::get_vk_user_info( $id_list );
        die( ObjectHelper::ToJSON( array( 'response' => $res )));
    }
}
