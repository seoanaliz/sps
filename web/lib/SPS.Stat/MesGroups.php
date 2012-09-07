<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class MesGroups
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
            $sql = 'SELECT group_id, name, general, name FROM ' .
                        TABLE_MES_GROUPS
                . ' WHERE group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger('@group_id', $groupId);
            $ds = $cmd->Execute();
            $ds->Next();

            return array (
                'groupId'   =>  $ds->getValue( 'group_id', TYPE_INTEGER ),
                'general'   =>  $ds->getValue( 'general',  TYPE_INTEGER ),
                'name'      =>  $ds->getValue( 'name',     TYPE_STRING  ),
            );
        }

        public static function get_groups( $userId )
        {
            $sql = 'SELECT c.group_id, c.name, c.general
                    FROM '
                          . TABLE_MES_GROUP_USER_REL . ' as b,
                        ' . TABLE_MES_GROUPS . ' as c
                    WHERE
                       c.group_id = b.group_id
                        AND b.user_id = @user_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@user_id', $userId );
            $ds = $cmd->Execute();
            $res = array();

            while($ds->Next()) {
                $res[] = array(
                    'group_id'  =>  $ds->getValue( 'group_id', TYPE_INTEGER ),
                    'general'   =>  $ds->getValue( 'general',  TYPE_INTEGER ),
                    'name'      =>  $ds->getValue( 'name' ),
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

                    $query = 'INSERT INTO ' . TABLE_MES_GROUP_USER_REL . '(user_id,group_id)
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
                            ' . TABLE_MES_GROUP_USER_REL . '
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

        public static function implement_entry( $groupId, $entry_id )
        {
            $sql = 'INSERT INTO ' . TABLE_MES_GROUP_DIALOG_REL . '(dialog_id,group_id)
                       VALUES (@dialog_id,@group_id)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->SetInteger('@dialog_id', $entry_id);
            $cmd->Execute();
        }

        public static function get_group_users( $group_id )
        {
            $sql = 'SELECT * FROM ' . TABLE_MES_GROUP_USER_REL . ' WHERE group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id', $group_id  );
            $ds = $cmd->Execute();

            $group_users = array();
            while( $ds->Next() ) {
                $group_users[] = $ds->GetValue( 'group_id', TYPE_INTEGER );
            }

            return $group_users;
        }

        public static function extricate_entry( $group_id, $entry_id )
        {
            $sql =  'DELETE FROM '
                . TABLE_MES_GROUP_DIALOG_REL . '
                       WHERE
                            group_id=@group_id AND dialog_id=@dialog_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id',  $group_id  );
            $cmd->SetInteger( '@dialog_id', $entry_id );
            $cmd->Execute();
        }

        public static function setGroup( $ava, $groupName, $comments, $groupId = false )
        {
            if ( $groupId ) {
                $sql = 'UPDATE
                            ' . TABLE_MES_GROUPS .
                    ' SET
                        "name"=@name
                      WHERE
                        group_id=@group_id';

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id',   $groupId);
                $cmd->SetString('@name',        $groupName);
                $cmd->ExecuteNonQuery();

            //create new
            } elseif( $groupName ) {
                $sql = 'INSERT INTO
                        ' . TABLE_MES_GROUPS . '
                            ( "name" )
                        VALUES
                            ( @name )
                        RETURNING
                            group_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetString('@name',        $groupName);
                $ds = $cmd->Execute();
                $ds->next();
                $groupId = $ds->getValue('group_id', TYPE_INTEGER);
                if ( !$groupId || $groupId== NULL ) {
                    return false;
                }

                return $groupId;
            }
            return false;
        }

        public static function check_group_name_free( $user_id, $group_name )
        {
            $sql = 'SELECT a.group_id
                    FROM
                    '  . TABLE_MES_GROUP_USER_REL . ' as a
                    , ' . TABLE_MES_GROUPS . ' as b
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
                            ' . TABLE_MES_GROUP_USER_REL . '
                         WHERE
                                group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger('@group_id', $group_id);
            if ( $cmd->ExecuteNonQuery() )
                return true;
            return false;
        }

        public static function get_users_dialogs( $user_id )
        {
            $sql = 'SELECT * FROM ' . TABLE_MES_DIALOGS . ' WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $user_id);
            $ds = $cmd->Execute();
            $ids = array();

            while( $ds->Next() ) {
                $a  =  $ds->GetValue( 'rec_id', TYPE_INTEGER );
                $id =  $ds->GetValue( 'id', TYPE_INTEGER );
                $ids[ $a ] = $id;
            }
            return $ids;
        }

        //возвращает группы, в которых состоит диалог
        public static function get_dialog_group( $dialog_id )
        {
            $sql = 'SELECT group_id FROM ' . TABLE_MES_GROUP_DIALOG_REL . ' WHERE dialog_id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@dialog_id', $dialog_id);
            $ds = $cmd->Execute();
            $res = array();
            while ( $ds->Next() ) {
                $res[] = $ds->GetValue( 'group_id', TYPE_INTEGER );
            }

            return $res;
        }

        //возвращает массив, ключи - id юзеров, значения - id групп
        public static function get_dialog_groups_ids_array( $user_id )
        {
            $ids = self::get_users_dialogs( $user_id );

            foreach( $ids as $k => &$v ) {
               $group_id = self::get_dialog_group( $v );
                $v = $group_id ? $group_id : '-1';
            }
            return $ids;
        }

        public static function get_group_dialogs( $group_id, $limit, $offset = 0 )
        {
            $limit =  $limit ? $limit : 1000;

            $sql = 'SELECT rec_id FROM '
                        . TABLE_MES_GROUP_DIALOG_REL . ' as a, '
                        . TABLE_MES_DIALOGS . ' as b
                    WHERE
                        a.group_id=@group_id AND
                        a.dialog_id=b.id
                    ORDER BY
                        rec_id
                    LIMIT  @limit
                    OFFSET @offset';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->SetInteger( '@limit', $limit );
            $cmd->SetInteger( '@offset', $offset );
            $ds = $cmd->Execute();

            $res = array();
            while ( $ds->Next() ) {
                $res[] =  $ds->GetValue( 'rec_id', TYPE_INTEGER );
            }
            return $res;
        }
    }
?>
