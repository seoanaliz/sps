<?php


class markMes
{
    //помечает сообщения как прочитанное или не прочитанное (если unread = 1)
    //строка номеров сообщений через запятую
    public function execute()
    {
//        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $mess_id        =   Request::getInteger( 'mids' );
        $unread         =   Request::getInteger( 'unread' );

        $unread    =   $unread ? 1 : 0;

        if ( !$mess_id || !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        if ( MesDialogs::toggle_read_unread( $user_id, $mess_id, $unread ) )
            die( ObjectHelper::ToJSON( array( 'response' => true ) ) );
        else
            die( ObjectHelper::ToJSON( array( 'response' => false ) ) );

    }
}
