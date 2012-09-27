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
        $timeout   =   Request::getInteger( 'timeout' );
        $times     =   Request::getInteger( 'ts' );
        $timeout   =   $timeout ? $timeout : 15;

        if ( !$user_id ) {
            die(ERR_MISSING_PARAMS);
        }

        $events  = MesDialogs::watch_dog( $user_id, $timeout, $times );
        if ( $events == 'no access_token' )
            die( ObjectHelper::ToJSON( array( 'response' => false )));

        $ids = MesGroups::get_dialog_groups_ids_array( $user_id );
        $result = array();
        foreach( $events->updates as $event ) {
            $status = 'offline';
            $event =  (array) $event ;
            $stat = isset( $event[0] ) ? $event[0] : 4;
            $attach = array();

            switch ( $stat ) {
                case 3:
                    $from_id   = isset( $event[3] )? $event[3] : $event['uid'];
                    $dialog_id = MesDialogs::get_dialog_id( $user_id, $from_id );
                    $result[]  = array(
                        'type'    => 'read',
                        'content' => array(
                            'mid'       =>  isset( $event[1] ) ? $event[1] : $event['mid'],
                            'from_id'   =>  $user_id,
                            'dialog_id' =>  MesDialogs::get_dialog_id( $user_id, $from_id ),
                            'groups'    =>  $ids[ $from_id ],
                        )
                    );
                    MesDialogs::set_state( $dialog_id, 0 );
                    break;
                case 4:
                    $from_id  = isset( $event[3] ) ? $event[3] : $event['uid'];
                    MesDialogs::set_dialog_ts( $user_id, $from_id, $event[5], !( $event[2] & 2 ), 0 );
                    if ( isset( $event[7]->attach1_type )) {
                        $message = MesDialogs::get_group_dilogs_list( $user_id, array( $from_id ));
                        $attach = reset( $message )->attachments;
                    } elseif ( isset( $event[7]->fwd )) {
                        $mid     = array( $event[1] );
                        $message =reset( MesDialogs::get_messages( $user_id, $mid ));
                        foreach ( $message->fwd_messages as $mess ) {
                            $fwd[] = array( 'body'      =>  $mess->body,
                                            'from_id'   =>  reset( StatUsers::get_vk_user_info( $mess->uid )),
                                            'date'      =>  $mess->body,
                            );
                        }
                    }
                    $result[] = array(
                        'type'    => $event[2] & 2 ? 'outMessage' : 'inMessage',
                        'content' => array(
                            'body'      =>  isset( $event[6] )? $event[6] : $event['body'],
                            'mid'       =>  isset( $event[1] )? $event[1] : $event['mid'],
                            'date'      =>  isset( $event[4] )? $event[4] : $event['date'],
                            'from_id'   =>  reset( StatUsers::get_vk_user_info( $from_id )),
                            'dialog_id' =>  MesDialogs::get_dialog_id( $user_id, $from_id ),
                            'groups'    =>  $ids[ $from_id ],
                            'attachments'=>  $attach,
                            'fwd'       =>  $fwd,
                        )
                    );

                    break;
                case 8:
                    $status = 'online';
                case 9:
                    $result[] = array(
                        'type'      =>  $status,
                        'content'   =>  array(
                            'userId'    =>  reset( StatUsers::get_vk_user_info(trim( $event[1], '-' )))
                        )
                    );
                    break;
                case 61:
                    $result[] = array(
                        'type'      =>  'typing',
                        'content'   =>  array(
                            'userId'        =>  reset( StatUsers::get_vk_user_info(trim( $event[1], '-' ))),
                            'start_typing'  =>  $event[2]
                        )
                    );
            }
        }
        $result = array_reverse($result);
        header( "Content-Type: text/javascript; charset=" . LocaleLoader::$HtmlEncoding );
        $res =  ObjectHelper::ToJSON( array( 'response' =>array( 'events' => $result, 'ts'=> $events->ts  )));
        if( $cb )
            $res = $cb . '(' . ObjectHelper::ToJSON( array( 'response' =>array( 'events' => $result, 'ts'=> $events->ts  ))) . ');';
//        echo  $cb . '(' . ObjectHelper::ToJSON( array( 'response' =>array( 'events' => $result, 'ts'=> $events->ts  ))) . ');';
        die( $res );
    }
}
