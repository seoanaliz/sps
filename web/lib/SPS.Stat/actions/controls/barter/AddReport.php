<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */
class AddReport
{

    public function execute()
    {
//        error_reporting(0);
        $now = time();
        $target_public_id   =   Request::getString ( 'targetPublicId' );
        $barter_public_id   =   Request::getString ( 'barterPublicId' );
        $start_looking_time =   Request::getInteger( 'startTime' ) ? Request::getInteger( 'startTime' ) : $now ;
        $stop_looking_time  =   Request::getInteger( 'stopTime' );
//        $user_id            =   Request::getInteger( 'userId' );
        $group_id           =   Request::GetInteger( 'groupId' );
        $approve            =   Request::getBoolean( 'approve' );
        $barter_id          =   Request::getInteger( 'reportId' );
        $start_looking_time -=  900;
        $user_id = AuthVkontakte::IsAuth();

        if ( !$group_id ) {
            $default_group = GroupsUtility::get_default_group( $user_id, 1 );
            $group_id = $default_group->group_id;
        }
//        if ( !$target_public_id || !$barter_public_id || !$start_looking_time || !$user_id || !$group_id ) {
        if ( !$target_public_id || !$barter_public_id || !$start_looking_time ) {
            die(ERR_MISSING_PARAMS);
        }

        if ( !GroupsUtility::is_author( $group_id, $user_id ))
            die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes' => 'access denied' )));

        $info = StatBarter::get_page_name( array( $target_public_id, $barter_public_id ));
        if ( empty( $info ))
            die( ObjectHelper::ToJSON( array('response' => 'false','err_mes' => 'wrong publics data')));
        //проверяем, нет ли схожих активных событий( есть - вернет их );
        // $approve - подтверждение на создание, пока всегда 0
//        if (  $barter_id && !$approve ) {
//            //todo проверка по времени?
//
//            $repeat_check = StatBarter::get_concrete_events( $info['target']['id'], $info['barter']['id'], 1 );
//            if ( !empty( $repeat_check ))
//                die( ObjectHelper::ToJSON( array('response' => 'matches','matches' => StatBarter::form_response( $repeat_check ))));
//        }

        $repeat_check = BarterEventFactory::Get(array('barter_public' => $info['barter']['id'], 'target_public' => $info['target']['id'], '_status'=>array( 1,2,3 )));
        if ( !empty( $repeat_check ))
            die( ObjectHelper::ToJSON( array('response' => 'matches','matches' => StatBarter::form_response( $repeat_check ))));

        if( $barter_id )
            $barter_event = BarterEventFactory::GetById( $barter_id, null, 'tst');
        else
            $barter_event = new BarterEvent();
        $barter_event->barter_public =  $info['barter']['id'];
        $barter_event->target_public =  $info['target']['id'];
        $barter_event->status        =  $start_looking_time ? 1 : 2;
        $barter_event->search_string =  $info['target']['shortname'];
        $barter_event->barter_type   =  1;
        $barter_event->start_search_at =  date( 'Y-m-d H:i:s', $start_looking_time );
        $stop_looking_time = $stop_looking_time ?
            date( 'Y-m-d H:i:s', $stop_looking_time ) : date( 'Y-m-d 23:59:59', $now );
        $barter_event->stop_search_at  =  $stop_looking_time;
        $barter_event->created_at  = date ( 'Y-m-d H:i:s', $now );
        $barter_event->standard_mark = true;
        $barter_event->groups_ids  = array( $group_id );
        $barter_event->creator_id  = $user_id;


        //разэталониваем предыдущие события такого рода
        if ( !$barter_id ) {
                $standard_check = StatBarter::get_concrete_events( $info['target']['id'], $info['barter']['id'], 0, 1 );
            if ( !empty( $standard_check )) {
                foreach( $standard_check as $entry ) {
                    if ( $entry->standard_mark ) {
                        $entry->standard_mark = false;
                        BarterEventFactory::Update( $entry, array(),'tst');
                    }
                }
            }
        }

        //делаем последнее
        if( $barter_id ) {

            BarterEventFactory::Update( $barter_event, array( BaseFactory::WithReturningKeys => true ), 'tst' );
        } else {
            BarterEventFactory::Add( $barter_event, array( BaseFactory::WithReturningKeys => true ), 'tst' );
        }

        if ( $barter_event->barter_event_id ) {
            $barter_event = BarterEventFactory::GetById( $barter_event->barter_event_id );
            die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( array( $barter_event )))));
        } else
            die(  ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'something goes wrong' )));
    }

}
