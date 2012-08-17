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
            $users  =   array();
            $params = array(
                'uids'   =>  $ids,
                'fields' =>  'photo,online',
            );

            $result = VkHelper::api_request( 'users.get', $params );


            foreach( $result as $user )
            {
                $users[$user->uid] = array(
                                    'userId'    =>  $user->uid,
                                    'ava'       =>  $user->photo,
                                    'name'      =>  $user->first_name . ' ' . $user->last_name,
                                    'online'    =>  $user->online,
                                );
            }

            return $users;
        }



        public static function add_user( $user )
        {


            if ( !is_array( $user ) )
                $users = self::get_vk_user_info( $user );

            foreach($users as $user) {
            $sql = 'INSERT INTO ' . TABLE_STAT_USERS .
                    '( user_id, name, ava,  comments )
                        VALUES
                             ( @userId, @name, @ava, @comments )';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger( '@userId',      $user['userId']);

                $cmd->SetString ( '@name',        $user['name'] );
                $cmd->SetString ( '@ava',         $user['ava'] );
                $cmd->SetString ( '@comments',    $user['comments'] );
                $res = $cmd->ExecuteNonQuery();
            }
            if ( !$res )
                return false;
            else
                return $user;
        }

        public static function get_user($user_id)
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


            $id = $id ? $id : AuthVkontakte::IsAuth();;
            //$token = Session::getString( 'access_token' );

//            if ( $token ) {
//                echo 'from seession';
//                return $token;
//            }
            $sql = 'SELECT access_token FROM ' . TABLE_STAT_USERS . ' WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@user_id', $id );
            $ds = $cmd->Execute();
            $ds->Next();

            $acc_tok = $ds->getValue( 'access_token', TYPE_STRING );

//            if ( $acc_tok ) {
//                print_r( Session::setString( 'access_token', $acc_tok ) );
//                print_r($acc_tok);
//            }
            return $acc_tok ? $acc_tok : false;
        }

        public static function set_access_token( $user_id, $acess_token )
        {
            $sql = 'UPDATE ' . TABLE_STAT_USERS . ' SET access_token=@access_token WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetString(  '@access_token', $acess_token );
            $ds = $cmd->Execute();
            $ds->Next();

            $acc_tok = $ds->getValue( 'access_token', TYPE_STRING );
        }
    }
?>
