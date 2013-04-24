<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    new stat_tables();


    class StatGroups
    {

        const GROUP_ORDINARY = 0;
        const GROUP_DEFAULT  = 2;
        const GROUP_GLOBAL   = 3;

        public static function is_general( $groupId )
        {
            $group = self::get_group( $groupId );
            if ( $group['general'] == 1 )
                return true;
            return false;
        }

        public static function get_group( $groupId )
        {
            $sql = 'SELECT group_id, name, general, name, comments,type, group_admin FROM ' . TABLE_STAT_GROUPS
                 . ' WHERE group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger('@group_id', $groupId);
            $ds = $cmd->Execute();
            $ds->Next();
            return array (
                'groupId'   =>  $ds->getInteger( 'group_id' ),
                'general'   =>  $ds->getBoolean( 'general'),
                'name'      =>  $ds->getValue(   'name' ),
                'comments'  =>  $ds->getValue(   'comments' ),
                'type'      =>  $ds->getInteger( 'type' ),
            );

        }

        public static function get_groups( $userId )
        {
            $sql = 'SELECT DISTINCT( c.group_id), c.type, c.name, c.comments, c.general, c.group_admin, b.fave
                    FROM
                        ' . TABLE_STAT_USERS . ' as a,
                        ' . TABLE_STAT_GROUP_USER_REL . ' as b,
                        ' . TABLE_STAT_GROUPS . ' as c
                    WHERE
                        ( a.user_id = b.user_id OR general  )
                        AND c.group_id = b.group_id
                        AND a.user_id = @user_id
                    ORDER BY c.name';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $cmd->SetInteger( '@user_id', $userId );
            $ds  = $cmd->Execute();
            $res = array();


            while( $ds->Next()) {
                $res[$ds->GetInteger( 'group_id' )] = array(
                    'group_id'  =>  $ds->GetInteger( 'group_id' ),
                    'general'   =>  $ds->GetBoolean( 'general'),
                    'name'      =>  $ds->GetValue( 'name' ),
                    'comments'  =>  $ds->getValue( 'comments' ),
                    'fave'      =>  $ds->GetBoolean( 'general' ),
                    'group_type'=>  $ds->GetInteger( 'type' ),
                );
            }
            $res = array_values( $res );

            ksort( $res );
            return $res;
        }

        public static function check_group_name_used( $user_id, $group_name )
        {
            $sql = 'SELECT a.group_id
                    FROM
                    '  . TABLE_STAT_GROUP_USER_REL . ' as a
                    , ' . TABLE_STAT_GROUPS . ' as b
                    WHERE
                        a.group_id = b.group_id
                        AND a.user_id = @user_id
                        AND b.name = @group_name';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@user_id',      $user_id);
            $cmd->SetString ( '@group_name',   $group_name);

            $ds = $cmd->Execute();

            $ds->Next();
            $a = $ds->GetInteger( 'group_id' );
            if ( $a )
                return $a;

            return false;
        }

        public static function implement_group( $groupIds, $userIds )
        {
            if ( !is_array( $userIds ) )
                $userIds = array ( $userIds );
            if ( !is_array( $groupIds ) )
                $groupIds = array ( $groupIds );
		
            $i = 0;
            foreach( $groupIds as $gr_id ) {
                $group = self::get_group( $gr_id );
                if ( $group['type'] == self::GROUP_GLOBAL )
                    continue;
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
            $group = self::get_group( $group_id );
            if ( $group['type'] == self::GROUP_GLOBAL )
                return true;
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

        public static function implement_entry( $group_id, $publicId, $user_id = 0 )
        {
            $group = self::get_group( $group_id );
            if ( $group['type'] == self::GROUP_GLOBAL )
                return true;
            //надо ли отмечать, кто добавил паблик в лист(да - если лист общий и паблик нигде не состоит)
            $listed_by = null;
            if( $group['general'] ) {
                $check = self::get_public_lists( $publicId );
                if( empty( $check )) {
                    $listed_by = $user_id;
                }
            }
            $sql = 'INSERT INTO ' . TABLE_STAT_GROUP_PUBLIC_REL . '( public_id, group_id, listed_by )
                       VALUES ( @public_id, @group_id, @listed_by )';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id',  $group_id);
            $cmd->SetInteger('@public_id', $publicId);
            $cmd->SetInteger('@listed_by', $listed_by);
            $cmd->Execute();
        }

        public static function extricate_entry( $group_id, $entry_id, $user_id = 0 )
        {
            $group = self::get_group( $group_id );
            if ( $group['type'] == self::GROUP_GLOBAL )
                return true;
            $query =  'DELETE FROM '
                . TABLE_STAT_GROUP_PUBLIC_REL . '
                           WHERE
                                group_id=@group_id AND public_id=@publ_id';

            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $group_id);
            $cmd->SetInteger('@publ_id', $entry_id);
            $cmd->Execute();
        }

        public static function setGroup( $ava, $groupName, $comments, $group_id = false )
        {
            //update
            if ( $group_id ) {
                $group = self::get_group( $group_id );
                if ( $group['type'] == self::GROUP_GLOBAL )
                    return true;
                $sql = 'UPDATE
                            ' . TABLE_STAT_GROUPS .
                    ' SET
                                "name"=@name, comments=@comments, ava=@ava,type=1
                          WHERE group_id=@group_id';

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id',   $group_id);
                $cmd->SetString('@name',        $groupName);
                $cmd->SetString('@comments',    $comments);
                $cmd->SetString('@ava',         $ava);
                $cmd->Execute();
                return $group_id;
            //create new
            } elseif( $groupName ) {
                $sql = 'INSERT INTO
                        ' . TABLE_STAT_GROUPS . '
                            ("name", comments, ava,general)
                        VALUES
                            (@name, @comments, @ava,false)
                        RETURNING
                            group_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetString('@name',        $groupName);
                $cmd->SetString('@comments',    $comments);
                $cmd->SetString('@ava',         $ava);

                $ds = $cmd->Execute();
                $ds->next();
                $group_id = $ds->getValue('group_id', TYPE_INTEGER);
                if ( !$group_id ) {
                    return false;
                }

                return $group_id;
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

        public static function delete_group( $group_id )
        {
            $group = self::get_group( $group_id );
            if ( $group['type'] == self::GROUP_GLOBAL )
                return true;
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

        public static function get_group_publics( $group_id, $limit, $offset = 0 )
        {
            $limit =  $limit ? $limit : 1000;

            $sql = 'SELECT vk_id,ava,name FROM '
                        . TABLE_STAT_GROUPS . ' as a, '
                        . TABLE_STAT_GROUP_PUBLIC_REL . ' as bs
                    WHERE
                        a.group_id=@group_id AND
                        a.vk_id=b.public_id AND
                    ORDER BY
                        quantity
                    OFFSET @offset
                    LIMIT  @limit';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->SetInteger( '@limit', $limit );
            $cmd->SetInteger( '@offset', $offset );
//            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();

            $res = array();
            while ( $ds->Next() ) {
                $res[] =  $ds->GetValue( 'rec_id', TYPE_INTEGER );
            }
            return $res;
        }

        public static function get_public_lists( $public_id,  $userId = 0 )
        {
            $groups = array();
            $sql = "SELECT DISTINCT(a.group_id) from "
                . TABLE_STAT_GROUP_USER_REL   . " AS a,
                 " . TABLE_STAT_GROUP_PUBLIC_REL . " AS b,
                 " . TABLE_STAT_GROUPS . " AS c
                 WHERE
                        a.group_id=b.group_id
                    AND a.group_id=c.group_id
                    AND ( user_id=@user_id OR c.general )
                    AND b.public_id=@public_id";

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@user_id',  $userId );
            $cmd->SetInteger( '@public_id',  $public_id );
            $ds = $cmd->Execute();
            while ( $ds->next() ) {
                $groups[] = $ds->getValue('group_id', TYPE_INTEGER);
            }
            return $groups;
        }

        public static function set_listed_by( $groupId, $publicId, $userId)
        {
            $sql = "UPDATE " . TABLE_STAT_GROUP_PUBLIC_REL . "
                    SET
                        listed_by = @userId
                    WHERE
                            groupId   = @groupId
                        AND publicId  = @publicId";

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@userId',   $userId );
            $cmd->SetInteger( '@groupId',  $groupId );
            $cmd->SetInteger( '@publicId', $publicId );
            $cmd->Execute();
        }
    }
?>
