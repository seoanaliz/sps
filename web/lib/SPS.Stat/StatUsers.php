<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );


    class StatUsers
    {

        public static function is_Sadmin($userId)
        {
            $user = self::get_user($userId);
            if ($user['rank'] == ADMIN_RANK)
                return true;
            return false;
        }

        public static function is_our_user($userId)
        {
            $user = self::get_user($userId);
            if ($user['userId'])
                return $user;
            return false;
        }

        public static function get_vk_user_info($userId)
        {
            $wr = new wrapper;
            $params = array(
                'uids'   =>  $userId,
                'fields' =>  'photo',
            );

            $result = $wr->vk_api_wrap('users.get', $params);
            $result = $result[0];

            return array(
                'userId'    =>  $result->uid,
                'ava'       =>  $result->photo,
                'name'      =>  $result->first_name . ' ' .$result->last_name

            );

        }

        public static function add_user($user)
        {

            $sql = 'INSERT INTO ' . TABLE_STAT_USERS .
                '( user_id, name, ava, rank, comments )
                    VALUES
                         ( @userId, @name, @ava, @rank, @comments )';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@userId',      $user['userId']);
            $cmd->SetInteger( '@rank',        $user['rank'] );
            $cmd->SetString ( '@name',        $user['name'] );
            $cmd->SetString ( '@ava',         $user['ava'] );
            $cmd->SetString ( '@comments',    $user['comments'] );
            $res = $cmd->ExecuteNonQuery();
            if ( !$res )
                return false;
            else
                return $user;
        }

        public static function get_user($userId)
        {
            $sql = 'SELECT user_id,name,rank,ava,comments FROM ' . TABLE_STAT_USERS
                 . ' WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $userId);
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

    }
?>
