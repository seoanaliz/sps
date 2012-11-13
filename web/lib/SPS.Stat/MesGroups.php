<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class MesGroups
    {
        //типы групп :
        // 0 - обычная
        // 1 - запросы в друзья
        // 2 - не в списке
        public static function check_group_type( $group_id )
        {
            $sql = 'SELECT type FROM '
                        . TABLE_MES_GROUPS .
                    ' WHERE group_id = @group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id', $group_id );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger('type');
        }

        public static function get_unlist_dialogs_group( $user_id )
        {
            $group_id = MesGroups::get_groups_by_type( $user_id, 2 );
            $group_id = $group_id[0];
            if ( !$group_id ) {
                $group_id =  MesGroups::setGroup( '', 'unlist', '' );
                MesGroups::set_group_type( $group_id, 2 );
                MesGroups::implement_group( $group_id, $user_id );
            }
            return $group_id;
        }

        public static function get_groups_by_type( $user_id, $type )
        {
            $sql = 'SELECT a.group_id
                    FROM
                    '   . TABLE_MES_GROUP_USER_REL . ' as a
                    , ' . TABLE_MES_GROUPS . ' as b
                    WHERE
                        a.group_id    = b.group_id
                        AND a.user_id = @user_id
                        AND b.type    = @group_type';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $cmd->SetInteger( '@user_id',    $user_id);
            $cmd->SetInteger( '@group_type', $type);
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[] = $ds->GetInteger( 'group_id' );
            }
            return $res;
        }

        public static function set_group_type( $group_id, $type )
        {
            $sql =  'UPDATE '
                     . TABLE_MES_GROUPS .
                    ' SET
                        type=@type
                     WHERE
                        group_id = @group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->SetInteger( '@type', $type );
            $ds = $cmd->Execute();

        }

        public static function get_group( $groupId )
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

        public static function get_groups( $userId, $type_selector = 0 )
        {
            $sql = 'SELECT c.group_id, c.name, c.type, b.read_mark
                    FROM '
                          . TABLE_MES_GROUP_USER_REL . ' as b,
                        ' . TABLE_MES_GROUPS . ' as c
                    WHERE
                        c.group_id = b.group_id
                        AND b.user_id = @user_id
                    ORDER BY
                        b.seq_number
                    ';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@user_id', $userId );
            $ds = $cmd->Execute();

            $res = array();
            $unread = MesGroups::get_unread_group_counters( $userId );
            $tmp_unread = 0;
            while( $ds->Next()) {
                $name = $ds->getValue( 'name' );
                $group_id = $ds->getInteger( 'group_id');
                $type     = $ds->getInteger( 'type' );
                if (( $type === 2  )  && !$type_selector ) {
                    $name = "Не в списке mkII";
                    $tmp_unread = isset( $unread[ $group_id ]) ? $unread[ $group_id ] : 0;
                    $ungroup_id = $group_id;
                    continue;
                }
                if (( $type === 1  )  && !$type_selector ) {
                    continue;
                }
                $res[] = array(
                    'group_id'  =>  $group_id,
                    'type'      =>  $type,
                    'name'      =>  $name,
                    'unread'    =>  isset( $unread[ $group_id ]) ? $unread[ $group_id ] : 0,
                    'isRead'    =>  MesGroups::get_highlighted_dialogs_quantity( $group_id, $userId ) > 1 ? false : true,
                );
            }

            ksort( $res );
            $res['ungrouped_unread'] = $tmp_unread;
            $res['ungrouped_group_id'] = $ungroup_id;
            return $res;
        }

        public static function update_highlighted_list( $group_ids, $user_id, $act, $dialog_ids )
        {
            $act = strtolower( $act ) == 'del' ? '-' : '+';
            if ( is_array( $dialog_ids ))
                $dialog_ids = implode( ',', $dialog_ids );
            if ( is_array( $group_ids ))
                $group_ids = implode( ',', $group_ids );
            $dialog_ids = '{' . $dialog_ids . '}';
            $group_ids  = '{' . $group_ids  . '}';
            $sql = 'UPDATE '
                        . TABLE_MES_GROUP_USER_REL .
                   ' SET
                         unread_dialogs_list = uniq(sort(unread_dialogs_list ' . $act . ' @dialog_ids))
                    WHERE
                        group_id= any(@group_id)
                        AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetString ( '@dialog_ids', $dialog_ids);
            $cmd->SetString( '@group_id' ,  $group_ids);
            $cmd->SetInteger( '@user_id',    $user_id);
            $cmd->Execute();
        }

        public static function delete_highlighted_list( $group_id, $user_id )
        {
            $sql = 'UPDATE '
                       . TABLE_MES_GROUP_USER_REL .
                  ' SET
                         unread_dialogs_list =  @dialog_ids,
                         last_clear_time     =  @time
                    WHERE
                        group_id=@group_id
                        AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString ( '@dialog_ids', '{0}' );
            $cmd->SetInteger( '@group_id' ,  $group_id );
            $cmd->SetInteger( '@user_id',    $user_id  );
            $cmd->SetInteger( '@time',       time());
            $cmd->Execute();
        }

        //&&
        public static function get_highlighted_dialogs_quantity( $group_id, $user_id )
        {
            $sql = 'SELECT
                        #unread_dialogs_list as quantity
                   FROM '

                    .   TABLE_MES_GROUP_USER_REL .
                '  WHERE
                        group_id=@group_id
                        AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id' ,  $group_id );
            $cmd->SetInteger( '@user_id',    $user_id);
//             $cmd->getQuery();
            $ds = $cmd->Execute();
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger('quantity');
        }

        public static function get_last_clear_time( $group_id, $user_id )
        {
            $sql = 'SELECT * FROM ' . TABLE_MES_GROUP_USER_REL . ' WHERE group_id=@group_id AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id', $group_id  );
            $cmd->SetInteger( '@user_id',  $user_id  );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->GetInteger( 'last_clear_time' );
        }

        public static function implement_group( $groupIds, $userIds )
        {
            $conn = ConnectionFactory::Get('tst');
            if ( !is_array( $userIds ))
                $userIds = array ( $userIds );
            if ( !is_array( $groupIds ))
                $groupIds = array ( $groupIds );

            $i = 0;
            foreach( $groupIds as $gr_id ) {
                foreach ( $userIds as $id ) {
                    $query = 'INSERT INTO ' . TABLE_MES_GROUP_USER_REL . '(user_id,group_id)
                          VALUES (@user_id,@group_id)';
                    $cmd = new SqlCommand( $query, $conn );
                    $cmd->SetInteger( '@group_id', $gr_id );
                    $cmd->SetInteger( '@user_id', $id );
                    if ( $cmd->ExecuteNonQuery())
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

        public static function implement_entry( $group_id, $entry_id, $user_id )
        {
            $sql = 'INSERT INTO ' . TABLE_MES_GROUP_DIALOG_REL . '(dialog_id,group_id)
                       VALUES (@dialog_id,@group_id)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger('@group_id', $group_id );
            $cmd->SetInteger('@dialog_id', $entry_id);
            $cmd->Execute();
            $unlist = self::get_unlist_dialogs_group( $user_id );
            self::extricate_entry( $unlist, $entry_id, $user_id );
        }

        public static function extricate_entry( $group_id, $entry_id, $user_id )
        {
            if (isset( $no_loops ))
                return true;
            //проверка на повторный вызов функции(без нее было бы кольцо)
            static $no_loops = 1;
            $sql =  'DELETE FROM '
                . TABLE_MES_GROUP_DIALOG_REL . '
                       WHERE
                            group_id=@group_id AND dialog_id=@dialog_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@group_id',  $group_id  );
            $cmd->SetInteger( '@dialog_id', $entry_id );
            $cmd->Execute();


            //проверяем, состоит ли группа еще в каких-либо листах. Нет - заносим ее в unlist
            $all_entry_groups = self::get_dialog_group( $entry_id );
            if ( empty($all_entry_groups)) {
                $unlist = self::get_unlist_dialogs_group( $user_id );
                self::implement_entry( $unlist, $entry_id, $user_id );
            }
        }

        public static function get_group_users( $group_id )
        {
            $sql = 'SELECT * FROM ' . TABLE_MES_GROUP_USER_REL . ' WHERE group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $cmd->SetInteger( '@group_id', $group_id  );
            $ds = $cmd->Execute();

            $group_users = array();
            while( $ds->Next() ) {
                $group_users[] = $ds->GetValue( 'group_id', TYPE_INTEGER );
            }

            return $group_users;
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

        public static function check_group_name_used( $user_id, $group_name )
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
            $cmd->SetInteger( '@user_id',      $user_id);
            $cmd->SetString ( '@group_name',   $group_name);
            $ds = $cmd->Execute();

            $ds->Next();
            $a = $ds->GetInteger( 'group_id' );
            if ( $a )
                return $a;

            return false;
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

        public static function get_group_dialogs( $user_id, $group_id, $limit, $offset = 0, $only_unr_out = 0 )
        {
            $where = $only_unr_out ? ' AND state=4' : '';
            $limit =  $limit ? $limit : 1000;

            $sql = 'SELECT rec_id FROM '
                        . TABLE_MES_GROUP_DIALOG_REL . ' as a, '
                        . TABLE_MES_DIALOGS . ' as b
                    WHERE
                        a.group_id=@group_id AND
                        a.dialog_id=b.id AND
                        rec_id is not NULL AND
                        b.user_id=@user_id  ' . $where . '
                    ORDER BY
                        last_update DESC
                    OFFSET @offset
                    LIMIT  @limit';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->SetInteger( '@limit', $limit );
            $cmd->SetInteger( '@offset', $offset );
            $cmd->SetInteger('@user_id', $user_id);
            $ds = $cmd->Execute();
            $res = array();
            while ( $ds->Next() ) {
                $res[] =  $ds->GetValue( 'rec_id', TYPE_INTEGER );
            }
            return $res;
        }

        //delete
        public static function get_ungroup_dialogs( $user_id, $limit, $offset = 0, $only_unr_out = 0 )
        {
            $where = $only_unr_out ? ' AND state=4' : '';
            $sql = 'SELECT
                        rec_id, id
                    FROM '
                        . TABLE_MES_DIALOGS . ' as b
                    LEFT JOIN '
                        . TABLE_MES_GROUP_DIALOG_REL . ' as a
                    ON b.id = a.dialog_id
                    WHERE
                        a.group_id IS NULL AND b.user_id=@user_id ' . $where . '
                    ORDER BY
                        last_update DESC
                    OFFSET
                        @offset
                    LIMIT
                        @limit';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));

            $cmd->SetInteger( '@limit', $limit );
            $cmd->SetInteger( '@offset', $offset );
            $cmd->SetInteger( '@user_id', $user_id);
            $ds = $cmd->Execute();
            $res = array();
            while ( $ds->Next() ) {
                $res[$ds->GetInteger( 'id')] =  $ds->GetInteger( 'rec_id' );

            }
            return $res;
        }

        public static function get_unread_group_counters( $user_id )
        {
            $sql = 'SELECT
                        count(group_id),group_id
                    FROM '
                      . TABLE_MES_DIALOGS . ' as a,  '
                      . TABLE_MES_GROUP_DIALOG_REL . ' as b
                    WHERE
                        user_id=@user_id AND a.id=b.dialog_id AND state=4
                    GROUP BY group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();
            $res = array();
            while ( $ds->Next()) {
                $res[$ds->GetValue( 'group_id', TYPE_INTEGER )] =  $ds->GetValue( 'count', TYPE_INTEGER );
            }
            return $res;
        }

        //delete
        public static function get_unread_ungr_counters( $user_id )
        {
            $sql = 'SELECT
                        count(*)
                    FROM '
                        . TABLE_MES_DIALOGS . ' as a
                    LEFT JOIN '
                        . TABLE_MES_GROUP_DIALOG_REL . ' as b ON a.id=b.dialog_id
                    WHERE
                        user_id=@user_id and group_id IS NULL and state=4';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger('@user_id', $user_id);
            $ds = $cmd->Execute();
            $ds->Next();

            return  $ds->GetValue( 'count', TYPE_INTEGER ) ? $ds->GetValue( 'count', TYPE_INTEGER ) : 0;
        }

        //delete
        public static function toggle_read_unread_gr( $user_id, $group_id, $read )
        {
            $sql = 'UPDATE '
                        . TABLE_MES_GROUP_USER_REL . '
                    SET
                        read_mark = @read
                    WHERE
                        user_id=@user_id
                        AND group_id=@group_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id',  $user_id  );
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->SetBoolean( '@read', $read );

            return $cmd->ExecuteNonQuery();
        }

        public static function set_list_place( $user_id, $group_id, $number )
        {
            $sql = 'UPDATE '
                        . TABLE_MES_GROUP_USER_REL .
                   ' SET
                        seq_number  = @number
                    WHERE
                        group_id    = @group_id
                        AND user_id = @user_id
                   ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id' , $user_id  );
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->SetInteger( '@number'  , $number   );
            return $cmd->ExecuteNonQuery();
        }

        public static function set_lists_order( $user_id, $group_ids )
        {
            $group_ids = explode( ',', $group_ids );
            $i = 2;
            foreach( $group_ids as $group_id ) {
                $type = MesGroups::check_group_type( $group_id );
                switch( $type) {
                    case 1:
                        MesGroups::set_list_place( $user_id, $group_id, 1 );
                        break;
                    case 2:
                        MesGroups::set_list_place( $user_id, $group_id, 0 );
                        break;
                    default:
                        MesGroups::set_list_place( $user_id, $group_id, $i++ );
                }
            }
        }
    }

?>
