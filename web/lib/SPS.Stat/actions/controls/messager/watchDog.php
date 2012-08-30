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
        if ( !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $events = MesDialogs::watch_dog( $user_id );
        if ( $events == 'no access_token' )
            die( ObjectHelper::ToJSON( array( 'response' => false )));

        $result = array();

        foreach( $events->updates as $event ) {
            $status = 'offline';
            switch ( $event[0] ) {
                case 4:
                    $result[] = array(
                        'type'    => 'inMessage',
                        'content' => array(
                            'body'      =>  $event[6],
                            'mid'       =>  $event[1],
                            'from_id'   =>  $event[3],
                            'dialog_id' =>  MesDialogs::get_dialog_id( $user_id, $event[3] ),
                            'date'      =>  $event[4],
                        )
                    );
                    break;
                case 8:
                    $status = 'online';
                case 9:
                    $result[] = array(
                        'type'      =>  $status,
                        'content'   =>  array(
                            'userId'    =>  trim( $event[1], '-')
                        )
                    );
            }
        }

        echo  ObjectHelper::ToJSON( array( 'response' => $result ));
        die();
//        print_r($result);
    }
}
