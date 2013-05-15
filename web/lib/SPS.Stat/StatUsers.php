<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    new stat_tables;
    class StatUsers
    {

        /**
        * trustworthy users
         */
        public static $editors_black_list = array(
            670456,
            191774732,
            106175502,
            196506553,
            176239625,
            13049517
        );

        const EDITOR_ROLE = 2;
        const USER_ROLE   = 0;

        public static function is_Sadmin( $userId )
        {
            $user = self::get_user($userId);
            if ($user['rank'] == self::EDITOR_ROLE ) {
                return true;
            }
            return false;
        }

        public static function is_our_user( $user_id )
        {
            $user = self::get_user( $user_id );
            if ( $user['userId'] )
                return $user;
            return false;
        }

        public static function get_vk_user_info( $ids, $user_id = '' )
        {
            if ( is_array( $ids ) )
                $ids    =   implode (',', $ids);
            $ids = trim( $ids );
            if ( !$ids )
                return array();

            if( $user_id )
                $acc_tok = StatUsers::get_access_token( $user_id );
            $users  =   array();
            $params =   array(
                'uids'   =>  $ids,
                'fields' =>  'photo,online',
            );
            if( isset( $acc_tok ) && $acc_tok )
                $params['access_token'] =   $acc_tok;

            $result = VkHelper::api_request( 'users.get', $params, 0 );
            if ( isset( $result->error ));
//                die( ERR_NO_ACC_TOK );

            foreach( $result as $user )
            {
                if( !isset ( $user->uid ))
                    continue;

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
                        '    ( user_id, name, ava,  comments, rank )
                         VALUES
                             ( @userId, @name, @ava, @comments, @rank )';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@rank',        StatUsers::USER_ROLE);
            $cmd->SetInteger( '@userId',      $users['userId']);
            $cmd->SetString ( '@name',        $users['name'] );
            $cmd->SetString ( '@ava',         $users['ava'] );
            $cmd->SetString ( '@comments',    $users['comments'] );
            echo $cmd->GetQuery();
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
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $id );
            $ds = $cmd->Execute();
            $ds->Next();
            $acc_tok = $ds->getValue( 'access_token', TYPE_STRING );
            return $acc_tok ? $acc_tok : false;
        }

        public static function set_access_token( $user_id, $access_token )
        {
            str_replace( '"', '', $access_token);
            $sql = 'UPDATE ' . TABLE_STAT_USERS . ' SET access_token=@access_token WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetString(  '@access_token', $access_token );
            $cmd->ExecuteNonQuery();
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

        public static function set_mes_limit_ts( $user_id, $forced = 0 )
        {
            $now = time();
            if ( !$forced )
                $now -= 86700;
            $sql = 'UPDATE
                    ' . TABLE_STAT_USERS . '
                    SET
                        mes_block_ts=@now
                    WHERE
                        user_id=@user_id
                        AND mes_block_ts < @now
                    ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $user_id );
            $cmd->SetInteger('@now', $now );
            $cmd->Execute();
            $sql = 'SELECT mes_block_ts FROM
                    ' . TABLE_STAT_USERS . '
                    WHERE
                        user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $user_id );
            $cmd->SetInteger('@now', $now );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->GetInteger('mes_block_ts');
        }

        public static function get_friendship_state( $user_id, $rec_ids )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                'access_token'  =>  $access_token,
                'uids'          =>  $rec_ids
            );
            $res = VkHelper::api_request( 'friends.areFriends', $params, 0 );
            return $res;
        }

        public static function manage_friend( $user_id, $rec_id, $add )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                die( ERR_NO_ACC_TOK );
            $res = VkHelper::api_request( $add ? 'friends.add' : 'friends.delete', array(
                'uid'           =>  $rec_id,
                'access_token'  =>  $access_token
            ), 0 );
            if (isset( $res->error ))
                return false;
            return true;
        }
    }
?>
