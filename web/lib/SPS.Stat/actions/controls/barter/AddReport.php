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
    /** Moscow time shift (mins) */
    const DEFAULT_TIMESHIFT = -240;

    public function execute()
    {
        error_reporting(0);
        $now = time();

        $target_public_id   =   Request::getString ( 'targetPublicId' );
        $barter_public_id   =   Request::getString ( 'barterPublicId' );
        $start_looking_time =   Request::getString ( 'startTime' ) ? Request::getString( 'startTime' ) : $now ;
        $stop_looking_time  =   Request::getString ( 'stopTime' );
        $group_id           =   Request::GetInteger( 'groupId' );
        $barter_id          =   Request::getInteger( 'reportId' );
        $time_shift         =   Request::getInteger( 'timeShift');
        $user_id            =   AuthVkontakte::IsAuth();

        if ( !$target_public_id || !$barter_public_id || !$start_looking_time ) {
            die(ERR_MISSING_PARAMS);
        }

        $default_group = GroupsUtility::get_default_group( $user_id, 1 );
        if ( !$group_id ) {
            $group_id = $default_group->group_id;
        }

        if ( !GroupsUtility::is_author( $group_id, $user_id ))
            die( ObjectHelper::ToJSON( array( 'response' => 'access denied' )));
        $publics_info = StatBarter::get_page_name( array( $target_public_id, $barter_public_id ));
        if ( empty( $publics_info ))
            die( ObjectHelper::ToJSON( array('response' => 'wrong publics data')));

        //поправка на timezone. клиент присылает свой сдвиг, меняем его на московский
        $time_shift = ( self::DEFAULT_TIMESHIFT - $time_shift) * 60;
        $start_looking_time = explode( ',', $start_looking_time );
        $stop_looking_time  = explode( ',', $stop_looking_time  );
        $count = count($start_looking_time);

        $barter_events_array = array();
        for( $i = 0; $i < $count; $i++ ) {
            $start_looking_time[$i] -=  900;
            $start_looking_time[$i]  += $time_shift;

            if( !isset( $stop_looking_time[$i])) {
                $stop_looking_time[$i] =  strtotime( 'midnight next day' );
            } else {
                $stop_looking_time[$i]   += $time_shift;
            }

            if ( $stop_looking_time[$i] < $start_looking_time[$i])
                $stop_looking_time = $start_looking_time[$i] + 84600;
            if ( $start_looking_time[$i] <= time()- 900 )
                die(  ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'too late' )));

            $barter_event = new BarterEvent();
            $repeat_check = $this->repeat_check( $publics_info['target']['id'], $publics_info['barter']['id'], $start_looking_time[$i], $stop_looking_time[$i], $user_id );
            if( $repeat_check )
                continue;
            $barter_event->barter_public =  $publics_info['barter']['id'];
            $barter_event->target_public =  $publics_info['target']['id'];
            $barter_event->status        =  $start_looking_time[$i] ? 1 : 2;
            $barter_event->search_string =  $publics_info['target']['shortname'];
            $barter_event->barter_type   =  1;
            $barter_event->start_search_at =  date( 'Y-m-d H:i:s', $start_looking_time[$i] );
            $barter_event->stop_search_at  =  date( 'Y-m-d H:i:s', $stop_looking_time[$i]  );
            $barter_event->created_at      =  date( 'Y-m-d H:i:s', $now );
            $barter_event->standard_mark = true;
            $barter_event->groups_ids  = array( $group_id );
            $barter_event->creator_id  = $user_id;
            $barter_events_array[] = $barter_event;

        }


        if( $barter_id ) {
            $check = BarterEventFactory::Update( $barter_event, array( BaseFactory::WithReturningKeys => true ), 'tst' );
        } else {
            $check = BarterEventFactory::AddRange( $barter_events_array, array( BaseFactory::WithReturningKeys => true ), 'tst' );
        }

        if ( $repeat_check )
            die( ObjectHelper::ToJSON( array('response' => 'matches','matches' => StatBarter::form_response( $repeat_check, $default_group->group_id ))));

        if ( $check ) {
            die( ObjectHelper::ToJSON( array('response' => true )));
        } else
            die(  ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'something went wrong' )));
    }

    private function repeat_check( $target_public_id, $barter_public_id, $start_time, $stop_time, $creator_id )
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
                    AND creator_id = @creator_id
                ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $cmd->SetString( '@start_time',     $start_time );
        $cmd->SetString( '@stop_time',      $stop_time );
        $cmd->SetString( '@barter_public',  $barter_public_id );
        $cmd->SetString( '@target_public',  $target_public_id );
        $cmd->SetString( '@creator_id',     $creator_id );
        $ds = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        if( $ds->Next()) {
            return array( BaseFactory::GetObject( $ds, BarterEventFactory::$mapping, $structure ));
        }
        return false;
    }
}
