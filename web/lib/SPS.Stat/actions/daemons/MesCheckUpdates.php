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
        $im_users = StatUsers::get_im_users();
        $this->check_new_messages( $im_users);
        foreach( $im_users as $user ) {
            MesDialogs::check_friend_requests( $user );
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
                MesDialogs::set_dialog_ts( $user, $dialog->uid, $dialog->date, !$dialog->out, $dialog->read_state );
            }
            sleep(0.4);
        }
    }
}
