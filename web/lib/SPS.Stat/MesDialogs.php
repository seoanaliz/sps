<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );


    class MesDialogs
    {

        public static function get_dialogs( $id )
        {

            $params = array(
                                'access_token'      =>  StatUsers::get_access_token( $id ),
                                'count'             =>  100,
                                'preview_lenght'    =>  50,
            );

            $offset = 0;
            $dialogs_array = array();

            while (1) {
                $params['offset'] = $offset;
                $res = VkHelper::api_request('messages.getDialogs', $params);
                $count = $res[0];
                unset( $res[0] );
                $offset += 100;
                $dialogs_array = array_merge( $dialogs_array, $res );

                if ( $count < $offset)
                    break;
            }


            return( $dialogs_array );
        }

        public static function addDialog( $user_id, $rec_id, $status )
        {

            $sql = 'INSERT INTO '
                                . TABLE_MES_DIALOGS . '( user_id, rec_id, status_id )
                            VALUES
                                    ( @user_id,@rec_id,@status_id )
                            RETURNING id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetInteger( '@rec_id', $rec_id );
            $cmd->SetString ( '@status_id', $status );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->GetValue( 'id', TYPE_INTEGER ) ;
        }

        public static function get_opponent( $user_id, $dialog_id )
        {
            $sql = 'SELECT rec_id FROM ' . TABLE_MES_DIALOGS
                . ' WHERE id = @dialog_id AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->GetValue( 'rec_id', TYPE_INTEGER ) ;
        }

        public static function writeMessage( $user_id, $rec_id, $text )
        {
            $params = array (
                'access_token'  =>  StatUsers::get_access_token($user_id),
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

            $params = array (

                    'access_token'  =>  StatUsers::get_access_token($user_id),
                    'uid'           =>  $rec_id,
                    'offset'        =>  $offset,
                    'count'         =>  $limit
            );

            $result = VkHelper::api_request('messages.getHistory', $params);
            if ( $result[0] == 0 )
                return false;
            unset ( $result[0] );

            return $result;
        }

        public static function toggle_read_unread( $user_id, $mess_ids, $unread )
        {
            $method = $unread ? 'markAsNew' : 'markAsRead';

            if ( is_array( $mess_ids ) )
                $mess_ids = implode( ',', $mess_ids );

            $params = array (

                    'access_token'  =>  StatUsers::get_access_token( $user_id ),
                    'mids'           =>  $mess_ids,
            );

            $res = VkHelper::api_request('messages.' . $method, $params, 0);
            if ( isset( $res->error ) )
                return false;
            return true;
        }

        public static function get_last_activity( $user_id, $rec_id )
        {
            $params = array (

                    'access_token'  =>  StatUsers::get_access_token($user_id),
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


    }
?>
