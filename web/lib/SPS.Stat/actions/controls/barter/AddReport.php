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
        $now = time();

        $target_public_id   =   Request::getString ( 'targetPublicId' );
        $barter_public_id   =   Request::getString ( 'barterPublicId' );
        $start_looking_time =   Request::getInteger( 'startTime' ) ? Request::getInteger( 'startTime' ) : $now ;
        $stop_looking_time  =   Request::getInteger( 'stopTime' );
        if ( !$target_public_id || !$barter_public_id || !$start_looking_time ) {
            die(ERR_MISSING_PARAMS);
        }

        $info = $this->get_page_name( array( $target_public_id, $barter_public_id ));
        $barter_event = new BarterEvent();
        $barter_event->barter_public = $info['barter']['id'];;
        $barter_event->target_public =  $info['target']['id'];
        $barter_event->status        = $start_looking_time ? 1 : 2;
        $barter_event->search_string =  $info['target']['shortname'];
        $barter_event->barter_type   = 1;
        $barter_event->start_search_at =  date( 'Y-m-d H:i:s', $start_looking_time );
        $stop_looking_time = $stop_looking_time ?
            date( 'Y-m-d H:i:s', $stop_looking_time ) : date( 'Y-m-d 00:00:00', $now + 84600 );;
        $barter_event->stop_search_at  =  $stop_looking_time;
        $barter_event->created_at  = date ( 'Y-m-d H:i:s', $now );

        BarterEventFactory::Add( $barter_event , array( BaseFactory::WithReturningKeys => true ), 'tst' );
        print_r($barter_event);
        if ( $barter_event->barter_event_id ) {
            die( ObjectHelper::ToJSON( array('response' => StatBarter::form_response( array( $barter_event )))));
        } else
            die(  ObjectHelper::ToJSON( array( 'response' => false, 'err_mes'   =>  'something goes wrong' )));
    }

    public function get_page_name( $urls )
    {
        $search = array( '/^(club)/', '/^(public)/');

        $query_line = array();
        foreach( $urls as $url ) {
            $url = parse_url( $url );
            $url = ltrim( $url['path'], '/' );
            $query_line[] = preg_replace( $search, '', $url );
        }

        $info = StatPublics::get_publics_info( $query_line );
        return  array( 'target' =>  reset( $info ),
                       'barter' =>  end( $info ));
    }

}
