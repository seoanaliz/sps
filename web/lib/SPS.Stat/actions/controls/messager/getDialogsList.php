<?php


class getDialogsList
{

    public function execute()
    {
        error_reporting( 0 );

        $user_id        =   Request::getInteger( 'userId' );
        $only_unread    =   Request::getInteger( 'unread' );
        $date_start     =   Request::getInteger( 'fromDate' );
        $date_end       =   Request::getInteger( 'toDate' );
        $offset         =   Request::getInteger( 'offset' );
        $limit          =   Request::getInteger( 'limit' );
        $in_mess        =   Request::getInteger( 'inMess' );
        $out_mess       =   Request::getInteger( 'outMess' );

        $offset         =   $offset ? $offset : 0;
        $limit          =   $limit  ?  $limit  :   25;
        $only_unread    =   $only_unread ? 1 : 0;
        $date_start     =   $date_start ? $date_start : 0;
        $date_end       =   $date_end ? $date_end : 2000000000;
        $in_mess        =   $in_mess  ? 1 : 0;
        $out_mess       =   $out_mess ? 1 : 0;

        if ( !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $dialogs_array = array();
        $row_dialog_array =  MesDialogs::get_dialogs( $user_id );

        $i = 0;
        $user_ids = '';
        foreach ( $row_dialog_array as $dialog ) {
            if (   ( $dialog->read_state == 1 && $only_unread)
                || ( $dialog->date > $date_end || $dialog->date < $date_start )
                || ( $dialog->out &&  $in_mess && !$out_mess  )
                || ( !$dialog->out &&  !$in_mess && $out_mess  ) );
            else {
                $i ++;
                if ( $i < $offset )
                    continue;
                $dialogs_array[] = $dialog;
                $user_ids .= $dialog->uid . ',';
                if ( $i == $offset + $limit )
                    break;
            }


        }
        unset($dialog);

        $users_array = StatUsers::get_vk_user_info( $user_ids );

        foreach( $dialogs_array as &$dialog ) {
             $dialog->uid = $users_array[ $dialog->uid ];
        }

        print_r($dialogs_array);
//        die( ObjectHelper::ToJSON(array('response' => $dialogs_array ) ) );

    }
}
