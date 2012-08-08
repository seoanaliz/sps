<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class StatGroups
    {
        public static function is_general( $groupId )
        {
            $group = self::get_group( $groupId );
            if ( $group['general'] == 1 )
                return true;
            return false;
        }

        private static function get_group( $groupId )
        {
            $sql = 'SELECT group_id, name, general, name, comments, group_admin FROM ' . TABLE_STAT_GROUPS
                 . ' WHERE group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger('@group_id', $groupId);
            $ds = $cmd->Execute();
            $ds->Next();

            return array (
                'groupId'   =>  $ds->getValue( 'group_id', TYPE_INTEGER ),
                'general'   =>  $ds->getValue( 'general',  TYPE_INTEGER ),
                'name'      =>  $ds->getValue( 'name',     TYPE_STRING  ),
                'comments'  =>  $ds->getValue( 'comments', TYPE_STRING  ),
            );

        }

        public static function get_groups( $userId )
        {
            $sql = 'SELECT c.group_id, c.name, c.comments, c.general, c.group_admin, b.fave
                    FROM
                        ' . TABLE_STAT_USERS . ' as a,
                        ' . TABLE_STAT_GROUP_USER_REL . ' as b,
                        ' . TABLE_STAT_GROUPS . ' as c
                    WHERE
                        a.user_id = b.user_id
                        AND c.group_id = b.group_id
                        AND a.user_id = @user_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@user_id', $userId );
            $ds = $cmd->Execute();
            $res = array();

            while($ds->Next()) {
                $res[] = array(
                    'group_id'  =>  $ds->getValue( 'group_id', TYPE_INTEGER ),
                    'general'   =>  $ds->getValue( 'general',  TYPE_INTEGER ),
                    'name'      =>  $ds->getValue( 'name' ),
                    'comments'  =>  $ds->getValue( 'comments' ),
                    'fave'      =>  $ds->GetBoolean( 'fave' ),
                );
            }

            ksort( $res );
            return $res;
        }

        public static function implement_group( $groupIds, $userIds )
        {
            if ( !is_array( $userIds ) )
                $userIds = array ( $userIds );
            if ( !is_array( $groupIds ) )
                $groupIds = array ( $groupIds );

            $i = 0;
            foreach( $groupIds as $gr_id ) {
                foreach ( $userIds as $id ) {

                    $query = 'INSERT INTO ' . TABLE_STAT_GROUP_USER_REL . '(user_id,group_id)
                          VALUES (@user_id,@group_id)';
                    $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                    $cmd->SetInteger('@group_id', $gr_id);
                    $cmd->SetInteger('@user_id', $id);
                    if ($cmd->ExecuteNonQuery())
                        $i++;

                }
            }

            if ($i > 0)
                return true;
            else
                return false;

        }

        public static function extricate_group( $group_id, $user_id )
        {
            $sql = 'DELETE FROM
                            ' . TABLE_STAT_GROUP_USER_REL . '
                         WHERE
                                group_id=@group_id
                                AND user_id=@user_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger('@group_id', $group_id);
            $cmd->SetInteger('@user_id', $user_id);

            if ($cmd->ExecuteNonQuery())
                return true;
            return false;
        }

        public static function implement_entry( $groupId, $publicId )
        {
            $sql = 'INSERT INTO ' . TABLE_STAT_GROUP_PUBLIC_REL . '(public_id,group_id)
                       VALUES (@public_id,@group_id)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->SetInteger('@public_id', $publicId);
            $cmd->Execute();
        }

        public static function extricate_entry( $group_id, $entry_id )
        {
            $query =  'DELETE FROM '
                . TABLE_STAT_GROUP_PUBLIC_REL . '
                           WHERE
                                group_id=@group_id AND public_id=@publ_id';

            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $group_id);
            $cmd->SetInteger('@publ_id', $entry_id);
            $cmd->Execute();
        }

        public static function setGroup( $ava, $groupName, $comments, $groupId = false )
        {
            //update
            if ($groupId) {
                $sql = 'UPDATE
                            ' . TABLE_STAT_GROUPS .
                    ' SET
                                "name"=@name, comments=@comments, ava=@ava
                          WHERE group_id=@group_id';

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id',   $groupId);
                $cmd->SetString('@name',        $groupName);
                $cmd->SetString('@comments',    $comments);
                $cmd->SetString('@ava',         $ava);
                $cmd->Execute();

            //create new
            } elseif($groupName) {
                $sql = 'INSERT INTO
                        ' . TABLE_STAT_GROUPS . '
                            ("name", comments, ava)
                        VALUES
                            (@name, @comments, @ava)
                        RETURNING
                            group_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetString('@name',        $groupName);
                $cmd->SetString('@comments',    $comments);
                $cmd->SetString('@ava',         $ava);

                $ds = $cmd->Execute();
                $ds->next();
                $groupId = $ds->getValue('group_id', TYPE_INTEGER);
                if (!$groupId || $groupId== NULL) {
                    return false;
                }

                return $groupId;
            }
        }

        public static function select_main_admin( $groupId, $public_id, $adminId )
        {
            $sql = 'UPDATE
                        ' . TABLE_STAT_GROUP_PUBLIC_REL . '
                    SET
                        main_admin=@admin_id
                    WHERE
                        group_id=@group_id AND public_id=@public_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id',    $groupId);
            $cmd->SetInteger('@public_id',   $public_id);
            $cmd->SetInteger('@admin_id',    $adminId);
            $cmd->Execute();
        }

        public static function check_group_name_free( $user_id, $group_name )
        {
            $sql = 'SELECT a.group_id
                    FROM
                    ' . TABLE_STAT_GROUP_USER_REL . ' as a
                    , ' . TABLE_STAT_GROUPS . ' as b
                    WHERE
                        a.group_id = b.group_id
                        AND a.user_id = @user_id
                        AND b.name = @group_name';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id',      $user_id);
            $cmd->SetString ('@group_name',   $group_name);
            $ds = $cmd->Execute();
            $ds->Next();

            if ($a = $ds->getValue( 'group_id' , TYPE_INTEGER ))
                return false;

            return true;

        }

        public static function delete_group( $group_id )
        {
            $sql = 'DELETE FROM
                            ' . TABLE_STAT_GROUP_USER_REL . '
                         WHERE
                                group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@group_id', $group_id );
            if ($cmd->ExecuteNonQuery())
                return true;
            return false;
        }



    }
?>
