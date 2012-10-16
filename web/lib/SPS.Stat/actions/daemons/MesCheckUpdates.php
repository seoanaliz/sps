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
        $this->check_new_messages( $im_users );
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

    public function check_new_messages( $im_users )
    {
        foreach( $im_users as $user ) {
            $dialogs = MesDialogs::get_all_dialogs( $user, 200 );

            if ( !$dialogs )
                continue;
            foreach( $dialogs as $dialog ) {
                if (isset($dialog->chat_id) )
                    continue;
                $dialog_id = MesDialogs::get_dialog_id( $user, $dialog->uid );
                echo 'dialog ' . $dialog_id. '<br>';
                $state_our = MesDialogs::get_state( $dialog_id );
                $state = MesDialogs::calculate_state( !$dialog->out, $dialog->read_state );
                echo 'state ' . $state . '<br>';
                echo 'state_our ' . $state_our  . '<br>';

                $check_ts =  MesDialogs::get_dialog_ts( $user, $dialog->uid );
                echo $check_ts . '<br>';
                if ( $check_ts == $dialog->date || ( $state != $state_our && $state = 0 )  )
                    continue;

                echo 'hurray!!<br>';
                MesDialogs::set_dialog_ts( $user, $dialog->uid, $dialog->date, !$dialog->out, $dialog->read_state );

                $group_id = MesGroups::get_dialog_group( $dialog_id );
                echo 'group ' . $dialog->read_state . '<br>';

                MesGroups::toggle_read_unread_gr( $user, $group_id[0], $dialog->read_state ? true : false );
            }
            sleep(0.4);
        }
    }
}
