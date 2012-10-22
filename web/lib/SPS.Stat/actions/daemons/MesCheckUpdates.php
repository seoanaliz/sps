<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.09.12
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */
class MesCheckUpdates
{
    public function execute() {
        set_time_limit( 300 );
        error_reporting(0);

        $im_users = StatUsers::get_im_users();
        self::check_new_messages( $im_users );
            die();
        foreach( $im_users as $user ) {
//                $this->ungroup_transfer( $user );
            MesDialogs::check_friend_requests( $user );
        }
    }

    public function ungroup_transfer( $im_user )
    {
        $ungr_dialogs =  MesGroups::get_ungroup_dialogs( $im_user, 10000 );
        $group_id     = MesGroups::get_unlist_dialogs_group( $im_user );
        foreach( $ungr_dialogs as $dialog_id => $rec_id ) {
            MesGroups::implement_entry( $group_id, $dialog_id );
        }
    }

    public static function check_new_messages( $im_users )
    {
        foreach( $im_users as $user ) {
            $dialogs = MesDialogs::get_all_dialogs( $user, 50 );
            if ( !$dialogs )
                continue;
            foreach( $dialogs as $dialog ) {
                if ( isset( $dialog->chat_id ))
                    continue;
                $dialog_id = MesDialogs::get_dialog_id( $user, $dialog->uid );
                $group_ids = MesGroups::get_dialog_group( $dialog_id );
                $old_ts    = MesDialogs::get_dialog_ts( $user, $dialog->uid );
                $last_clear_time = MesGroups::get_last_clear_time( $group_ids[0], $user );
                $act = '';
                if ( !$dialog->read_state && !$dialog->out && $old_ts != $dialog->date && $last_clear_time < $dialog->date )
                    $act = 'add';
                elseif( $dialog->read_state && !$dialog->out || $dialog->out )
                    $act = 'del';

                if ( $act )

                    MesGroups::update_highlighted_list( $group_ids, $user, $act, $dialog_id );
                //если сообщение
                $check_ts =  MesDialogs::get_dialog_ts( $user, $dialog->uid );
                if ( $check_ts == $dialog->date )
                    continue;
                MesDialogs::set_dialog_ts( $user, $dialog->uid, $dialog->date, !$dialog->out, $dialog->read_state );
            }
            sleep(0.4);
        }
    }
}
