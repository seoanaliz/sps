<?php


class getOnline
{

    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $dialog_id     =   Request::getInteger( 'dialogId' );

        if ( !$user_id || !$dialog_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $rec_id = MesDialogs::get_opponent( $user_id, $dialog_id );
        if ( !$rec_id )
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'    =>  'dialog missing' ) ) );

        $res = MesDialogs::get_last_activity( $user_id, $rec_id );
        if ( is_array( $res ) )
             die( ObjectHelper::ToJSON( array( 'response' => $res ) ) );
        else
             die( ObjectHelper::ToJSON( array( 'response' => false ) ) );

    }
}
