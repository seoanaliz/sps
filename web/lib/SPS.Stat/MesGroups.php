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
            $sql = 'SELECT c.group_id, c.name, c.comments, c.general
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
                    'comments'  =>  $ds->getValue( 'comments' ),
                    'fave'      =>  $ds->GetBoolean( 'fave' ),
                );
            }

            ksort( $res );
            return $res;
        }

        public static function implement_group( $groupId, $userIds )
        {
            if ( !is_array( $userIds ) )
                $userIds = array ( $userIds );

            foreach ( $userIds as $id ) {
                $query = 'INSERT INTO ' . TABLE_MES_GROUP_USER_REL . '(user_id,group_id)
                      VALUES (@user_id,@group_id)';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id', $groupId);
                $cmd->SetInteger('@user_id', $id);
                if ( $cmd->ExecuteNonQuery() )
                    return true;
                else
                    return false;

            }

        }

        public static function implement_dialog( $group_id, $dialog_id )
        {
            $sql = 'INSERT INTO ' . TABLE_STAT_GROUP_PUBLIC_REL . '(dialog_id,group_id)
                       VALUES (@dialog_id,@group_id)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $group_id);
            $cmd->SetInteger('@dialog_id', $dialog_id);
            $cmd->Execute();
        }

        public static function setGroup( $groupName, $groupId = false )
        {
            //update
            if ( $groupId ) {
                $sql = 'UPDATE
                            ' . TABLE_MES_GROUPS .
                    ' SET
                                "name"=@name, comments=@comments, ava=@ava
                          WHERE group_id=@group_id';

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id',   $groupId);
                $cmd->SetString('@name',        $groupName);
                $cmd->Execute();

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
        }


        public static function check_group_name_free( $user_id, $group_name )
        {
            $sql = 'SELECT a.group_id
                    FROM
                    ' . TABLE_MES_GROUP_USER_REL . ' as a
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

    }
?>
