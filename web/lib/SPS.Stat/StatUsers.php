<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );


    class StatUsers
    {

        public static function is_Sadmin( $userId )
        {
            $user = self::get_user($userId);
            if ($user['rank'] == ADMIN_RANK)
                return true;
            return false;
        }

        public static function is_our_user( $userId )
        {
            $user = self::get_user( $userId );
            if ( $user['userId'] )
                return $user;
            return false;
        }

        public static function get_vk_user_info( $ids )
        {

            if ( is_array( $ids ) )
                $ids    =   implode (',', $ids);
            if ( !trim( $ids ))
                return array();
            $users  =   array();
            $params = array(
                'uids'   =>  $ids,
                'fields' =>  'photo,online',
            );

            $result = VkHelper::api_request( 'users.get', $params );

            foreach( $result as $user )
            {
                $users[ $user->uid ] = array(
                                    'userId'    =>  $user->uid,
                                    'ava'       =>  $user->photo,
                                    'name'      =>  $user->first_name . ' ' . $user->last_name,
                                    'online'    =>  $user->online,
                                );
            }

            return $users;
        }

        public static function add_user( $users )
        {
            if ( !isset( $users['userId'] )) {
                $users = self::get_vk_user_info( $users );
                $users = reset( $users );
            }

            $users['comment'] = isset( $users['comment'] ) ? $users['comment'] : '';
		    $sql =  'INSERT INTO ' . TABLE_STAT_USERS .
                        '    ( user_id, name, ava,  comments )
                         VALUES
                             ( @userId, @name, @ava, @comments )';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@userId',      $users['userId']);
            $cmd->SetString ( '@name',        $users['name'] );
            $cmd->SetString ( '@ava',         $users['ava'] );
            $cmd->SetString ( '@comments',    $users['comments'] );
            $res = $cmd->ExecuteNonQuery();

            if ( !$res )
                return false;
            else
                return $users;
        }

        public static function get_user( $user_id )
        {
            $sql = 'SELECT user_id,name,rank,ava,comments FROM ' . TABLE_STAT_USERS
                 . ' WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $user_id);
            $ds = $cmd->Execute();
            $ds->Next();

            return array (
                'userId'    =>  $ds->getValue('user_id', TYPE_INTEGER),
                'rank'      =>  $ds->getValue('rank', TYPE_INTEGER),
                'ava'       =>  $ds->getValue('ava', TYPE_STRING),
                'name'      =>  $ds->getValue('name', TYPE_STRING),
                'comments'  =>  $ds->getValue('comments', TYPE_STRING)

            );

        }

        public static function get_users()
        {
            $sql = 'SELECT user_id FROM ' . TABLE_STAT_USERS;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $usersIds = array();
            while ($ds->Next())
                $usersIds[] = $ds->getValue('user_id', TYPE_INTEGER);

            return $usersIds;

        }

        public static function get_access_token( $id )
        {
            $id = $id ? $id : AuthVkontakte::IsAuth();
            
            $sql = 'SELECT access_token FROM ' . TABLE_STAT_USERS . ' WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@user_id', $id );
            $ds = $cmd->Execute();
            $ds->Next();

            $acc_tok = $ds->getValue( 'access_token', TYPE_STRING );

            if ( $acc_tok ) {
            }
            return $acc_tok ? $acc_tok : false;
        }

        public static function set_access_token( $user_id, $access_token )
        {
            $sql = 'UPDATE ' . TABLE_STAT_USERS . ' SET access_token=@access_token WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetString(  '@access_token', $access_token );
            $ds = $cmd->ExecuteNonQuery();
//            $ds->Next();
//
//            $acc_tok = $ds->getValue( 'access_token', TYPE_STRING );
        }

        public static function get_im_users()
        {
            $sql = 'SELECT user_id,access_token FROM ' . TABLE_STAT_USERS . ' WHERE access_token<>\'\'';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $usersIds = array();
            while ( $ds->Next() )
                $usersIds[] = $ds->getValue( 'user_id', TYPE_INTEGER );

            return $usersIds;
        }
    }
?>
