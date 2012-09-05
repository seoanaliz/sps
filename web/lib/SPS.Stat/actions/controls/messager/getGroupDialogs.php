<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 05.09.12
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
class getGroupDialogs
{
   //возвращает участников диалогов выбранной группы
    public function execute()
    {
        error_reporting( 0 );
//        $user_id        =   Request::getInteger( 'userId' );
        $group_id       =   Request::getInteger( 'groupId' );
        if ( !$group_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $res_ids = MesGroups::get_group_dialogs( $group_id, 0 );
        $users_array = StatUsers::get_vk_user_info( $res_ids );

        $res = array();
        foreach( $users_array as $user ) {
            $dialog['uid'] = $user;
            $res[] = $dialog;
        }
        die( ObjectHelper::ToJSON( array( 'response' => $res )));

    }
}
