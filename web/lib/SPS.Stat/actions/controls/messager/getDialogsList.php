<?php


class getDialogsList
{
    public function execute()
    {
        error_reporting( 0 );
        $user_id        =   Request::getInteger( 'userId' );
        $group_id       =   Request::getInteger( 'groupId' );
        $only_unr_out   =   Request::getInteger( 'unreadOut' );
        $offset         =   Request::getInteger( 'offset' );
        $limit          =   Request::getInteger( 'limit' );
//        $in_mess        =   Request::getInteger( 'inMess' );
//        $out_mess       =   Request::getInteger( 'outMess' );
//        $ungrouped      =   Request::getInteger( 'ungrouped');
//        $date_start     =   Request::getInteger( 'fromDate' );
//        $date_end       =   Request::getInteger( 'toDate' );

        $group_id       =   $group_id ? $group_id : 0;
        $offset         =   $offset ? $offset : 0;
        $limit          =   $limit  ?  $limit  :   25;
        $only_unr_out    =   $only_unr_out ? 1 : 0;
//        $date_start     =   $date_start ? $date_start : 0;
//        $date_end       =   $date_end ? $date_end : 2000000000;
//        $in_mess        =   $in_mess  ? 1 : 0;
//        $out_mess       =   $out_mess ? 1 : 0;
//        $ungrouped      =   $ungrouped ? 1 : 0;
//        $ungrouped      =   $ungrouped ? 1 : 0;

        $dialogs_array = array();
        if ( !$group_id ) {
//            $row_dialog_array = MesDialogs::get_last_dialogs( $user_id, $offset, $limit );
            $res_ids            = MesGroups::get_ungroup_dialogs( $user_id, $limit, $offset );
            $row_dialog_array   = MesDialogs::get_group_dilogs_list( $user_id, $res_ids );
//            if ( !$row_dialog_array )
//                die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'no dialogs' )));
//            elseif( $row_dialog_array == 'no access_token' )
//                die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'user is not authorized' )));
        }
        else {
            $res_ids = MesGroups::get_group_dialogs( $user_id, $group_id, $limit, $offset, $only_unr_out );
            $row_dialog_array  = MesDialogs::get_group_dilogs_list( $user_id, $res_ids );
        }

        $user_ids = array();
//        $ids = MesGroups::get_dialog_groups_ids_array( $user_id );

        foreach ( $row_dialog_array as $dialog ) {
            $dialog->id = MesDialogs::get_dialog_id( $user_id, $dialog->uid );
            $dialog->groups = MesDialogs::get_rec_groups( $user_id, $dialog->uid );
            if( !$dialog->id ) {
                $state = MesDialogs::calculate_state( $dialog->read_state, !$dialog->out );
                MesDialogs::addDialog( $user_id, $dialog->uid, $dialog->date, $state, '');
            }
            if ( isset( $dialog->chat_id )
                  || ( $dialog->read_state == 1 && $only_unr_out )
//                || ( $dialog->date > $date_end || $dialog->date < $date_start )
//                || ( $dialog->out &&    $in_mess && !$out_mess )
//                || ( !$dialog->out &&  !$in_mess && $out_mess  )
//                || ( !$dialog->out &&  !$in_mess && $out_mess  )
//                || ( $group_id  && !in_array( $group_id, $dialog->groups ) )
//                || ( $ungrouped &&  $ids[ $dialog->uid ] != -1 )
            );
            else {
                unset( $dialog->attachment );
                if( !isset( $dialog->attachments ))
                    $dialog->attachments = array();
                $dialogs_array[] = $dialog;
                $user_ids[] = $dialog->uid;
            }
        }
        unset( $dialog );

        if ( count( $user_ids ) < 1 )
            die( ObjectHelper::ToJSON( array( 'response' => array())));

        $users_array = StatUsers::get_vk_user_info( $user_ids );

        foreach( $dialogs_array as &$dialog ) {
            $dialog->uid = $users_array[ $dialog->uid ];
        }

        die( ObjectHelper::ToJSON( array( 'response' => $dialogs_array )));
    }
}
