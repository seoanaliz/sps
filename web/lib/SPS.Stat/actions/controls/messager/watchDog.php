<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 07.08.12
 * Time: 12:37
 * To change this template use File | Settings | File Templates.
 */
class watchDog
{

    public function Execute() {
        error_reporting( 0 );
        $user_id   =   Request::getInteger( 'userId' );
        $cb        =   Request::getString ( 'callback' );
        if ( !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $events = MesDialogs::watch_dog( $user_id );
        if ( $events == 'no access_token' )
            die( ObjectHelper::ToJSON( array( 'response' => false )));

        $result = array();
        foreach( $events as $event ) {

            $status = 'offline';
            $event =  (array) $event ;

            $stat = isset( $event[0] ) ? $event[0] : 4;
            switch ( $stat ) {
                case 4:
                    $from_id = isset( $event[3] )? $event[3] : $event['uid'];
                    $result[] = array(
                        'type'    => 'inMessage',
                        'content' => array(
                            'body'      =>  isset( $event[6] )? $event[6] : $event['body'],
                            'mid'       =>  isset( $event[1] )? $event[1] : $event['mid'],
                            'date'      =>  isset( $event[4] )? $event[4] : $event['date'],
                            'from_id'   =>  $from_id,
                            'dialog_id' =>  MesDialogs::get_dialog_id( $user_id, $from_id )
                        )
                    );
                    break;
                case 8:
                    $status = 'online';
                case 9:
                    $result[] = array(
                        'type'      =>  $status,
                        'content'   =>  array(
                            'userId'    =>  trim( $event[1], '-' )
                        )
                    );
            }
        }
        $result = array_reverse($result);
        if ($cb) {
            echo  $cb . '(' . ObjectHelper::ToJSON( array( 'response' => $result )) . ');';
        } else {
            echo ObjectHelper::ToJSON( array( 'response' => $result ));
        }
        die();
//        print_r($result);
    }
}
