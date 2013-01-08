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
    const DEFAULT_TIMESHIFT = -240;
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
        $time_shift         =   Request::getInteger( 'timeShift');
        $start_looking_time -=  900;
        if ( $stop_looking_time && $stop_looking_time < $start_looking_time)
            $stop_looking_time += 84600;

        $user_id = AuthVkontakte::IsAuth();
        //Р±РµСЂРµРј РєРѕРЅС‚Р°РєС‚РѕРІСЃРєРёР№ timezone, РѕРЅ РѕС‚Р»РёС‡Р°РµС‚СЃСЏ РѕС‚ С„Р°РєС‚РёС‡РµСЃРєРѕРіРѕ РЅР° 1
        //todo Р±СЂР°С‚СЊ С‚РµРєСѓС‰РёР№ timezone С‡РµСЂРµР· js

        $time_shift = ( self::DEFAULT_TIMESHIFT - $time_shift) * 60;
        $start_looking_time  += $time_shift;
        $stop_looking_time   += $time_shift;

        $default_group = GroupsUtility::get_default_group( $user_id, 1 );
        if ( !$group_id ) {
            $group_id = $default_group->group_id;
        }
//        if ( !$target_public_id || !$barter_public_id || !$start_looking_time || !$user_id || !$group_id ) {
        if ( !$target_public_id || !$barter_public_id || !$start_looking_time ) {
            die(ERR_MISSING_PARAMS);
        }

        if ( !GroupsUtility::is_author( $group_id, $user_id ))
            die( ObjectHelper::ToJSON( array( 'response' => 'access denied' )));

        $info = StatBarter::get_page_name( array( $target_public_id, $barter_public_id ));
        if ( empty( $info ))
            die( ObjectHelper::ToJSON( array('response' => 'wrong publics data')));
        //РїСЂРѕРІРµСЂСЏРµРј, РЅРµС‚ Р»Рё СЃС…РѕР¶РёС… Р°РєС‚РёРІРЅС‹С… СЃРѕР±С‹С‚РёР№( РµСЃС‚СЊ - РІРµСЂРЅРµС‚ РёС… );
        // $approve - РїРѕРґС‚РІРµСЂР¶РґРµРЅРёРµ РЅР° СЃРѕР·РґР°РЅРёРµ, РїРѕРєР° РІСЃРµРіРґР° 0
//        if (  $barter_id && !$approve ) {
//            //todo РїСЂРѕРІРµСЂРєР° РїРѕ РІСЂРµРјРµРЅРё?
//
//            $repeat_check = StatBarter::get_concrete_events( $info['target']['id'], $info['barter']['id'], 1 );
//            if ( !empty( $repeat_check ))
//                die( ObjectHelper::ToJSON( array('response' => 'matches','matches' => StatBarter::form_response( $repeat_check ))));
//        }
        $repeat_check = $this->repeat_check($info['target']['id'], $info['barter']['id'], $start_looking_time, $stop_looking_time );
        if ( $repeat_check )
            die( ObjectHelper::ToJSON( array('response' => 'matches','matches' => StatBarter::form_response( $repeat_check, $default_group->group_id ))));

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
        $barter_event->groups_ids  = array( $group_id,1,2,3 );
        $barter_event->creator_id  = $user_id;


        //СЂР°Р·СЌС‚Р°Р»РѕРЅРёРІР°РµРј РїСЂРµРґС‹РґСѓС‰РёРµ СЃРѕР±С‹С‚РёСЏ С‚Р°РєРѕРіРѕ СЂРѕРґР°
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

        if( $barter_id ) {
            BarterEventFactory::Update( $barter_event, array( BaseFactory::WithReturningKeys => true ), 'tst' );
        } else {
            BarterEventFactory::Add( $barter_event, array( BaseFactory::WithReturningKeys => true ), 'tst' );
        }

        if ( $barter_event->barter_event_id ) {
            $barter_event = BarterEventFactory::GetById( $barter_event->barter_event_id );
            die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( array( $barter_event), $default_group->group_id))));
        } else
            die(  ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'something goes wrong' )));
    }

    private function repeat_check( $target_public_id, $barter_public_id, $start_time, $stop_time )
    {
        $start_time = date( 'Y-m-d H:i:s', $start_time );
        $stop_time  = date( 'Y-m-d H:i:s', $stop_time );
        $sql = 'SELECT * FROM
                    barter_events
                WHERE
                        barter_public = @barter_public
                    AND target_public = @target_public
                    AND (( start_search_at <= @start_time AND @start_time <= stop_search_at)
                      OR ( start_search_at <= @stop_time  AND @stop_time <= stop_search_at)
                      OR ( @start_time <= start_search_at AND stop_search_at <= @stop_time )
                    )
                    AND status in (1,2,3)
                ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $cmd->SetString( '@start_time', $start_time );
        $cmd->SetString( '@stop_time',  $stop_time );
        $cmd->SetString( '@barter_public',  $barter_public_id );
        $cmd->SetString( '@target_public',  $target_public_id );
        $ds = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        if( $ds->Next()) {
            return array( BaseFactory::GetObject( $ds, BarterEventFactory::$mapping, $structure ));
        }
        return false;
    }
}
