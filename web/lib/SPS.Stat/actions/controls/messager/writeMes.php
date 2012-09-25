<?php


class writeMes
{

    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $dialog_id      =   Request::getInteger( 'dialogId' );
        $text           =   Request::getString ( 'text' );

        if ( !$user_id || !$dialog_id || !$text) {
            die(ERR_MISSING_PARAMS);
        }

        $rec_id = MesDialogs::get_opponent( $user_id, $dialog_id );
        if ( !$rec_id )
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'    =>  'dialog missing' ) ) );

        $res = MesDialogs::writeMessage( $user_id, $rec_id, $text );
        if( $res === 'no access_token' )
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'user is not authorized' ) ) );
        elseif ( !isset($res->error_code )) {
            die( ObjectHelper::ToJSON( array( 'response' => $res )));
//            MesDialogs::set_state( $dialog_id, 0 );
        } elseif ( $res->error_code != 9 ) {
            MesDialogs::add_message_to_queue( $user_id, $dialog_id, $text );
            StatUsers::set_mes_limit_ts( $user_id );
            //todo обработка ошибок, капча, в частности
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'    =>  'add to queue' )));
        } elseif ( $res->error_code == 9 ) {
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'    =>  'flood control' )));
        } else {
            die( ObjectHelper::ToJSON( array( 'response' => false )));
        }
    }
}
