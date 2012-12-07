<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */
class deleteReport
{
    public function execute()
    {
        error_reporting(0);

        $barter_event_id   =    Request::getInteger( 'reportId' );
//        $user_id           =    Request::getInteger( 'userId' );
        $group_id          =    Request::getInteger( 'groupId' );

        $user_id = AuthVkontakte::IsAuth();
        if ( !$barter_event_id ) {
            die(ERR_MISSING_PARAMS);
        }

        if ( !GroupsUtility::is_author( $group_id, $user_id ))
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes' => 'access denied' )));

        $barter_event = BarterEventFactory::GetById( $barter_event_id );
        $barter_event->status = 7;
        BarterEventFactory::Update( $barter_event );
        die( ObjectHelper::ToJSON( array( 'response' => true )));

    }
}
