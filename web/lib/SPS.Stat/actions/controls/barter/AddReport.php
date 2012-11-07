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
        error_reporting(0);
        $now = time() + 10800;

        $barter_event = new BarterEvent();
        $target_public_id   =   Request::getString ( 'targetPublicId' );
        $barter_public_id   =   Request::getString ( 'barterPublicId' );
        $start_looking_time =   Request::getInteger( 'startTime' ) ? Request::getInteger( 'startTime' ) : $now ;
        $stop_looking_time  =   Request::getInteger( 'stopTime' );
        if ( !$target_public_id || !$barter_public_id ) {
            die(ERR_MISSING_PARAMS);
        }
        $request_line = $target_public_id . ',' . $barter_public_id;
        $publics_data  = StatPublics::get_publics_info( $request_line );
        $barter_public = reset( $publics_data );
        $target_public = end(   $publics_data );
        $barter_event->barter_public = $barter_public['id'];
        $barter_event->target_public = $target_public['id'];
        $barter_event->status        = $start_looking_time ? 1 : 2;
        $barter_event->search_string = $barter_public_id;
        $barter_event->barter_type   = 1;
        $barter_event->start_search_at =  date( 'Y-m-d H:i:s', $start_looking_time );
        $stop_looking_time  = $stop_looking_time ?
            $stop_looking_time : 2 * StatBarter::DEFAULT_SEARCH_DURATION + $start_looking_time;
        $barter_event->stop_search_at  =  date( 'Y-m-d H:i:s', $stop_looking_time );
        $barter_event->created_at  = date ( 'Y-m-d H:i:s', $now );
        $barter_event_id = BarterEventFactory::Add( $barter_event , array( BaseFactory::WithReturningKeys => true ), 'tst' );

        if ( $barter_event_id ) {
            $res = BarterEventFactory::GetById( $barter_event_id , null, null, 'tst' );
            die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( array( $res )))));
        } else
            die(  ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'something goes wrong' )));
    }

}
