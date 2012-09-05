<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );


    class MesDialogs
    {
        //to do execute добавить
        public static function get_last_dialogs( $id, $offset, $limit )
        {
            $access_token = StatUsers::get_access_token( $id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                                'access_token'      =>  $access_token,
                                'count'             =>  $limit,
                                'preview_lenght'    =>  50,
                                'fields'            =>  'has_mobile',
                                'offset'            =>  $offset
            );

            $offset = 0;
            $dialogs_array = array();

//            while ( 1 ) {
//                $params[ 'offset' ] = $offset;
                $res   = VkHelper::api_request( 'messages.getDialogs', $params );
                $count = $res[0];
                unset( $res[0] );
//                $offset += 100;

                $dialogs_array = array_merge( $dialogs_array, $res );
//                if ( $count < $offset )
//                    break;
//            }
            return( $dialogs_array );
        }

        //возвращает лист диалогов отдельной группы
        public static function get_group_dilogs_list( $rec_ids )
        {
            $code   = '';
            $return = "return{";
            foreach( $rec_ids as $id ) {
                $code   .= "var a$id = API.messages.getDialogs({\"uid\":$id }) ;";
                $return .= "\"a$id\":a$id,";
            }

            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute',  array( 'code'  =>  $code ));
            $result = array();
            foreach( $res as $dialog ) {
                if ( !isset($dialog[1] ))
                    continue;

                $result[] = $dialog[1];
            }
            return $result;
        }

        public static function addDialog( $user_id, $rec_id, $status )
        {
            $sql = 'INSERT INTO '
                        . TABLE_MES_DIALOGS . '( user_id, rec_id, status )
                    VALUES
                            ( @user_id,@rec_id,@status )
                    RETURNING id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetInteger( '@rec_id', $rec_id );
            $cmd->SetString ( '@status', $status );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetValue( 'id', TYPE_INTEGER ) ;
        }

        public static function get_dialog_id( $user_id, $rec_id )
        {
            $sql = 'SELECT id FROM ' . TABLE_MES_DIALOGS . ' WHERE rec_id=@rec_id AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@rec_id',  $rec_id  );
            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();
            $id = false;
            if ( $ds->Next() );
                $id =  $ds->GetValue( 'id', TYPE_INTEGER ) ;
            return $id ? $id : false;
        }

        public static function get_opponent( $user_id, $dialog_id )
        {
            $sql = 'SELECT rec_id FROM ' . TABLE_MES_DIALOGS
                . ' WHERE id = @dialog_id AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->SetInteger( '@user_id',   $user_id );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->GetValue( 'rec_id', TYPE_INTEGER ) ;
        }

        public static function writeMessage( $user_id, $rec_id, $text )
        {
            $acess_token = StatUsers::get_access_token( $user_id );
            if ( !$acess_token )
                return 'no access_token';

            $params = array (
                'access_token'  =>  $acess_token,
                'uid'           =>  $rec_id,
                'message'       =>  $text,
                //todo подумать о guid
            );

            $res = VkHelper::api_request( 'messages.send', $params, 0 );
            if ( isset( $res->error ) )
                return false;
            return true;
        }

        public static function get_specific_dialog( $user_id, $rec_id, $offset, $limit )
        {
            $acess_token = StatUsers::get_access_token( $user_id );
            if ( !$acess_token )
                return 'no access_token';

            $params = array (
                    'access_token'  =>  $acess_token,
                    'uid'           =>  $rec_id,
                    'offset'        =>  $offset,
                    'count'         =>  $limit
            );

            $result = VkHelper::api_request( 'messages.getHistory', $params );
            if ( $result[0] == 0 )
                return false;
            unset ( $result[0] );

            return $result;
        }


        public static function toggle_read_unread( $user_id, $mess_ids, $unread )
        {
            $method = $unread ? 'markAsNew' : 'markAsRead';

            if ( is_array( $mess_ids ))
                $mess_ids = implode( ',', $mess_ids );

            $params = array (
                    'access_token'  =>  StatUsers::get_access_token( $user_id ),
                    'mids'          =>  $mess_ids,
            );

            $res = VkHelper::api_request('messages.' . $method, $params, 0 );
            if ( isset( $res->error ) )
                return false;
            return true;
        }

        public static function get_last_activity( $user_id, $rec_id )
        {
            $params = array (
                    'access_token'  =>  StatUsers::get_access_token( $user_id ),
                    'uid'           =>  $rec_id,
            );

            $res = VkHelper::api_request( 'messages.getLastActivity', $params, 0);
            if ( isset( $res->error ) )
                return false;
            return (array) $res;
        }

        public static function set_status( $dialog_id, $status )
        {
            $sql = 'UPDATE ' . TABLE_MES_DIALOGS . ' SET status=@status WHERE id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->SetString ( '@status', $status );
            if ( $cmd->ExecuteNonQuery() )
                return true;
            return false;
        }

        private static function get_long_poll_server( $token )
        {
            $res = VkHelper::api_request( 'messages.getLongPollServer', array('access_token' => $token), 1 );
            if ( isset( $res->error ) )
                return false;
            return (array)$res;
        }

        public static function watch_dog( $user_id ){
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                'count'         =>  25,
                'filters'       =>  1,
                'time_offset'   =>  5,
                'access_token'  =>  $access_token
            );
            $res = VkHelper::api_request( 'messages.get', $params );
            array_shift( $res );
            return $res;
        }

        public static function watch_dog_long_pull( $user_id, $ts = 0 )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $a = self::get_long_poll_server( $access_token );
            if ( !$a )
                return false;
            $ts  = $ts ? $ts : $a['ts'];
            $url = "http://{$a['server']}?act=a_check&key={$a['key']}&ts=$ts&wait=25&mode=2";

            $res = json_decode( file_get_contents( $url ));

            return $res->updates;
        }
    }
?>
