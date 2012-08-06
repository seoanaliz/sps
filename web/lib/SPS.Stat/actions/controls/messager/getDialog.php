<?php


class getDialog
{

    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $dialog_id      =   Request::getInteger( 'dialogId' );
        $offset         =   Request::getInteger( 'offset' );
        $limit          =   Request::getInteger( 'limit' );

        $offset         =   $offset ? $offset  : 0;
        $limit          =   $limit  ?  $limit  :   25;

        if ( !$dialog_id || !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $rec_id = MesDialogs::get_opponent( $user_id, $dialog_id );
        if (!$rec_id)
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'    =>  'dialog missing' ) ) );

        $dialog_array  =  MesDialogs::get_specific_dialog( $user_id, $rec_id, $offset, $limit );

        if ( !$dialog_array )
            $dialog_array = array();
        print_r($dialog_array);
        die( ObjectHelper::ToJSON( array( 'response' => $dialog_array ) ) );

    }
}
