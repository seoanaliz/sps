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
        set_time_limit( 0 );
//        error_reporting(0);

        $im_users = StatUsers::get_im_users();
        MesDialogs::check_new_messages( $im_users );
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


}
