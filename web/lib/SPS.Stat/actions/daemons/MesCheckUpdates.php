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

        foreach( $im_users as $user ) {
            $dialogs = MesDialogs::get_all_dialogs( $user );

            if ( !$dialogs )
                continue;
            foreach( $dialogs as $dialog ) {
                if (isset($dialog->chat_id) )
                    continue;
                MesDialogs::set_dialog_ts( $user, $dialog->uid, $dialog->date, $dialog->out, $dialog->read_state );
            }
            sleep(0.4);
        }
    }
}
