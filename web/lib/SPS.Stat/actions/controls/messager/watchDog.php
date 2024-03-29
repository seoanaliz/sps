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
        header( "Content-Type: text/javascript; charset=" . LocaleLoader::$HtmlEncoding );
        $user_id   =   Request::getInteger( 'userId' );
        $cb        =   Request::getString ( 'callback' );
        $timeout   =   Request::getInteger( 'timeout' );
        $times     =   Request::getInteger( 'ts' );
        $timeout   =   $timeout ? $timeout : 15;

        if ( !$user_id ) {
            if( $cb )
                die ( $cb . '(' . ERR_MISSING_PARAMS . ');');
            die( ERR_MISSING_PARAMS );
        }

        $events = MesDialogs::watch_dog( $user_id, $timeout, $times );
        if ( $events == 'no access_token' ) {
            if( $cb )
                die ( $cb . '(' . ERR_NO_ACC_TOK . ');');
            die( ERR_NO_ACC_TOK );
        }

        $unl_group = MesGroups::get_unlist_dialogs_group( $user_id );
        $result = array();
        foreach( $events->updates as $event ) {
            $status = 'offline';
            $event =  (array) $event ;
            $stat = isset( $event[0] ) ? $event[0] : 4;
            $attach = array();

            switch ( $stat ) {
                case 3:
                    $from_id   = isset( $event[3] )? $event[3] :    $event['uid'];
                    $dialog_id = MesDialogs::get_dialog_id( $user_id, $from_id );
                    $groups_array = MesGroups::get_dialog_group( $dialog_id );
                    $result[]  = array(
                        'type'    => 'read',
                        'content' => array(
                            'mid'       =>  isset( $event[1] ) ? $event[1] : $event['mid'],
                            'from_id'   =>  $user_id,
                            'dialog_id' =>  MesDialogs::get_dialog_id( $user_id, $from_id ),
                            'groups'    =>  in_array( $unl_group, $groups_array ) ? array() : $groups_array,
                        )
                    );
                    MesDialogs::set_state( $dialog_id, 0, 0 );
                    break;
                case 4:

                    $from_id  = isset( $event[3] ) ? $event[3] : $event['uid'];
                    $dialog_id = MesDialogs::get_dialog_id( $user_id, $from_id );
                    if( !$dialog_id ) {
                        $dialog_id = MesDialogs::addDialog( $user_id, $from_id, time(), 4, '' );
                    }
                    $groups_array = MesGroups::get_dialog_group( $dialog_id );
                    MesDialogs::set_dialog_ts( $user_id, $from_id, time(), !( $event[2] & 2 ), 0 );
                    if ( isset( $event[7]->attach1_type )) {
                        $message = MesDialogs::get_group_dilogs_list( $user_id, array( $from_id ));
                        $attach = reset( $message )->attachments;
                    } elseif ( isset( $event[7]->fwd )) {
                        $mid     = array( $event[1] );
                        $message = reset( MesDialogs::get_messages( $user_id, $mid ));
                        foreach ( $message->fwd_messages as $mess ) {
                            $fwd[] = array( 'body'      =>  $mess->body,
                                            'from_id'   =>  reset( StatUsers::get_vk_user_info( $mess->uid, $user_id )),
                                            'date'      =>  $mess->body,
                            );
                        }
                    }


                    $text = isset( $event[6] )? $event[6] : $event['body'];
                    $mid  =  isset( $event[1] )? $event[1] : $event['mid'];
                    MesDialogs::save_last_line( $dialog_id, $text, !( $event[2] & 2 ), $mid, 0 );
                    if ( !( $event[2] & 2 ) ) {
                        MesGroups::update_highlighted_list( $groups_array, $user_id, 'add', $dialog_id );
                    }
                    $result[] = array(
                        'type'    => $event[2] & 2 ? 'outMessage' : 'inMessage',
                        'content' => array(
                            'body'      =>  $text,
                            'mid'       =>  $mid,
                            'date'      =>  time(),
                            'from_id'   =>  reset( StatUsers::get_vk_user_info( $from_id, $user_id )),
                            'dialog_id' =>  $dialog_id,
                            'groups'    =>  in_array( $unl_group ,$groups_array ) ? array() : $groups_array,
                            'attachments'=>  $attach,
                            'fwd'       =>  isset( $fwd ) ? $fwd : array(),
                        )
                    );
                    break;
                case 8:
                    $status = 'online';
                case 9:
                    $result[] = array(
                        'type'      =>  $status,
                        'content'   =>  array(
                            'userId'    =>  reset( StatUsers::get_vk_user_info(trim( $event[1], '-' ), $user_id ))
                        )
                    );
                    break;
                case 61:
                    $result[] = array(
                        'type'      =>  'typing',
                        'content'   =>  array(
                            'userId'        =>  reset( StatUsers::get_vk_user_info(trim( $event[1], '-' ), $user_id )),
                            'start_typing'  =>  $event[2]
                        )
                    );
                    break;
            }
        }
        $result = array_reverse($result);

        $res =  ObjectHelper::ToJSON( array( 'response' =>array( 'events' => $result, 'ts'=> $events->ts  )));
        if( $cb )
            $res = $cb . '(' . ObjectHelper::ToJSON( array( 'response' =>array( 'events' => $result, 'ts'=> $events->ts  ))) . ');';
        die( $res );
    }
}
