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
//        error_reporting(0);
        $now = time();

        $barter_event_id   =   Request::getInteger( 'barterId' );
        if ( !$barter_event_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $barter_event = BarterEventFactory::GetById( $barter_event_id );
        $barter_event->status = 6;
        BarterEventFactory::Update( $barter_event );
        die( ObjectHelper::ToJSON( array('response' => true)));

    }


}
