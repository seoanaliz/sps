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
        $group_id       =   Request::getInteger( 'groupId' );
        $user_id        =   Request::getInteger( 'userId' );
        if ( !$group_id || !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $res_ids = MesGroups::get_group_dialogs( $user_id, $group_id, 0 );
        $users_array = StatUsers::get_vk_user_info( $res_ids, $user_id );

        $res = array();
        foreach( $users_array as $user ) {
            $dialog['uid'] = $user;
            $dialog['id'] = MesDialogs::get_dialog_id( $user_id, $dialog['uid']['userId'] );
            $dialog['groups'] = MesGroups::get_dialog_group( $dialog['id']);
            $res[] = $dialog;
        }
        die( ObjectHelper::ToJSON( array( 'response' => $res )));
    }
}
