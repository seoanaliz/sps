<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    new stat_tables();


    class MesDialogs
    {
        //слить с  get_all_dialogs
        public static function get_last_dialogs( $id, $offset, $limit )
        {
            if ( !$limit )
                $limit = 25;
            $access_token = StatUsers::get_access_token( $id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                'access_token'      =>  $access_token,
                'count'             =>  $limit,
                'preview_lenght'    =>  50,
                'offset'            =>  $offset
            );

            $dialogs_array = array();
            $res   = VkHelper::api_request( 'messages.getDialogs', $params );
            unset( $res[0] );
            $dialogs_array = array_merge( $dialogs_array, $res );

            return( $dialogs_array );
        }

        //получает все диалоги, $max_offset - ограничение
        public static function get_all_dialogs( $user_id, $max_offset = 0 )
        {
            $max_offset = $max_offset ? $max_offset : 9000000;
            $access_token = StatUsers::get_access_token( $user_id );
            $offset = 0;
            $dialog_array = array();

            while(1) {
                $code   = '';
                $return = "return{";
                for( $i = 0; $i < 25; $i++ ) {
                    $code   .= "var a$i = API.messages.getDialogs({\"count\":200,\"offset\":$offset});";
                    $return .= "\"a$i\":a$i,";
                    $offset += 200;
                    if ( $offset >= $max_offset )
                        break;
                }

                $code .= trim( $return, ',' ) . "};";
                $res = VkHelper::api_request( 'execute',  array( 'code'  =>  $code, 'access_token' => $access_token ), 0 );
                //todo logs
                if ( isset( $res->error )) {
                    return false;
                }


                foreach ( $res as $stak ) {
                    unset( $stak[0] );
                    $dialog_array = array_merge( $dialog_array, $stak );
                }

                if ( count ( $res->a0 ) < 200 || $offset > $max_offset )
                    break;
                sleep(0.2);
            }
            $j = 0;
            foreach( $dialog_array as $dialog )
            {
                $state = MesDialogs::calculate_state( $dialog->read_state, !$dialog->out );
                if ( !MesDialogs::addDialog( $user_id, $dialog->uid, $dialog->date, $state, '' ))
                    $j++;
                if ( $j > $max_offset )
                    break;
            }
            return $dialog_array;
        }

        //возвращает лист диалогов отдельной группы
        public static function get_group_dilogs_list( $user_id, $rec_ids )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $code   = '';
            $return = "return{";
            foreach( $rec_ids as $id ) {
                $id = abs( $id );
                $code   .= "var a$id = API.messages.getDialogs({\"uid\":$id }) ;";
                $return .= "\"a$id\":a$id,";
            }

            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute',  array( 'code'  =>  $code, 'access_token' => $access_token ), 1 );
            if( isset($res->error ))
                return array();

            $result = array();
            foreach( $res as $dialog ) {
                if ( !isset( $dialog[1] ))
                    continue;

                $result[] = $dialog[1];
            }
            return $result;
        }

        public static function addDialog( $user_id, $rec_id, $last_update, $state, $status )
        {
            if ( self::get_dialog_id( $user_id, $rec_id ))
                return false;
            $default_group_id = MesGroups::get_groups_by_type( $user_id, 2 );
            $sql = 'INSERT INTO '
                        . TABLE_MES_DIALOGS . '( user_id, rec_id, status, last_update, state )
                    VALUES
                            ( @user_id,@rec_id,@status,@last_update, @state )
                    RETURNING id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetInteger( '@rec_id', $rec_id );
            $cmd->SetInteger( '@last_update', $last_update );
            $cmd->SetInteger( '@state',  $state  );
            $cmd->SetString ( '@status', $status );
            $ds = $cmd->Execute();
            $ds->Next();
            $dialog_id = $ds->GetValue( 'id', TYPE_INTEGER );

            $group_id = MesGroups::get_unlist_dialogs_group( $user_id);
            if ( $dialog_id ) {
                MesGroups::implement_entry( $group_id, $dialog_id, $user_id );
            }
            return $dialog_id  ;
        }

        //возвращает id диалога
        public static function get_dialog_id( $user_id, $rec_id )
        {
            $sql = 'SELECT id FROM ' . TABLE_MES_DIALOGS . ' WHERE rec_id=@rec_id AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@rec_id',  $rec_id  );
            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();
            if ( $ds->Next() );
                $id =  $ds->GetValue( 'id', TYPE_INTEGER ) ;
            return $id ? $id : false;
        }

        //возвращает id второго участника диалога
        public static function get_opponent( $user_id, $dialog_id )
        {
            $sql = 'SELECT rec_id FROM ' . TABLE_MES_DIALOGS
                . ' WHERE id = @dialog_id AND user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->SetInteger( '@user_id',   $user_id );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->GetValue( 'rec_id', TYPE_INTEGER ) ;
        }

        public static function writeMessage( $user_id, $rec_id, $text )
        {
            $acess_token = StatUsers::get_access_token( $user_id );
            if ( !$acess_token )
                return 'no access_token';

            $params = array (
                'access_token'  =>  $acess_token,
                'uid'           =>  $rec_id,
                'message'       =>  $text,
                //todo подумать о guid
            );

            $res = VkHelper::api_request( 'messages.send', $params, 0 );

            if ( isset( $res->error ))
                return $res->error;
            return $res;
        }

        //возвращает $limit(максимум 200) сообщений между юзером и $rec_id, начиная с $offset
        public static function get_specific_dialog( $user_id, $rec_id, $offset, $limit )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';

            $params = array (
                    'access_token'  =>  $access_token,
                    'uid'           =>  $rec_id,
                    'offset'        =>  $offset,
                    'count'         =>  $limit
            );

            $result = VkHelper::api_request( 'messages.getHistory', $params );
            if ( $result[0] == 0 )
                return false;
            unset ( $result[0] );

            return $result;
        }

        //возвращает массив сообщений по id, $message_ids - массив этих id
        public static function get_messages( $user_id, $message_ids )
        {
            $message_ids = implode ( ',', $message_ids );
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';

            $params = array(
                'access_token'  =>  $access_token,
                'mids'           =>  $message_ids
            );

            $result = VkHelper::api_request('messages.getById', $params, 0 );
            if ( $result[0] == 0 )
                return false;
            unset ( $result[0] );
            return $result;
        }

        public static function toggle_read_unread( $user_id, $mess_ids, $unread )
        {
            $method = $unread ? 'markAsNew' : 'markAsRead';
            if ( is_array( $mess_ids ))
                $mess_ids = implode( ',', $mess_ids );

            $params = array (
                    'access_token'  =>  StatUsers::get_access_token( $user_id ),
                    'mids'          =>  $mess_ids,
            );

            $res = VkHelper::api_request('messages.' . $method, $params, 0 );
            if ( isset( $res->error ) )
                return false;
            return true;

            //todo маркер прочитанности в бд
        }

        public static function get_last_online( $user_id, $rec_id )
        {
            $params = array (
                    'access_token'  =>  StatUsers::get_access_token( $user_id ),
                    'uid'           =>  $rec_id,
            );

            $res = VkHelper::api_request( 'messages.getLastActivity', $params, 0);
            if ( isset( $res->error ) )
                return false;
            return (array) $res;
        }

        //устанавливает ярлык диалогу
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

        public static function get_statuses( $user_id, $rec_ids ) {
            $rec_ids = '{' . implode( ',', $rec_ids  ) . '}';
            $sql = 'SELECT
                        rec_id, status
                    FROM '
                        . TABLE_MES_DIALOGS . '
                    WHERE
                       rec_id = ANY( @rec_ids )
                       AND user_id=@user_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString ( '@rec_ids', $rec_ids );
            $cmd->SetInteger( '@user_id', $user_id );
            $ds =  $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[$ds->GetInteger( 'rec_id' )] = $ds->GetValue( 'status' );
            }
            return $res;
        }

        //отслеживание новых сообщений, без лонгпулла
        public static function watch_dog_wo_long_pull( $user_id )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                'count'         =>  25,
                'filters'       =>  1,
                'time_offset'   =>  5,
                'access_token'  =>  $access_token
            );
            $res = VkHelper::api_request( 'messages.get', $params );
            array_shift( $res );
            return $res;
        }

        //отслеживание изменений лонгпуллом
        public static function watch_dog( $user_id, $timeout, $ts = 0 )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $a = self::get_long_poll_server( $access_token );
            if ( !$a )
                return false;
            $ts  = ($ts ? $ts : $a['ts']);
            $url = "http://{$a['server']}?act=a_check&key={$a['key']}&ts=$ts&wait=" . $timeout . "&mode=2";

            $res = json_decode( file_get_contents( $url ));
            return $res;
        }

        private static function get_long_poll_server( $token )
        {
            $res = VkHelper::api_request( 'messages.getLongPollServer', array('access_token' => $token), 0 );
            if ( isset( $res->error ) )
                return false;
            return (array)$res;
        }

        //возвращает список групп юзера, в которых состоит $rec_id
        public static function get_rec_groups( $user_id, $rec_id )
        {
            $sql = "SELECT DISTINCT(a.group_id) FROM "
                      . TABLE_MES_GROUP_USER_REL   . " as a, "
                      . TABLE_MES_GROUP_DIALOG_REL . " as b, "
                      . TABLE_MES_DIALOGS          . " as c
                    WHERE
                        c.id=b.dialog_id
                        AND a.group_id = b.group_id
                        AND a.user_id=@user_id and rec_id=@rec_id";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->SetInteger( '@rec_id', $rec_id );

            $ds =  $cmd->Execute();
            $res = array();
            while ( $ds->Next()) {
                $res[] = $ds->GetValue( 'group_id', TYPE_INTEGER );
            }
            return $res;
        }

        public static function set_dialog_ts( $user_id, $rec_id, $time, $in, $read )
        {
            $sql = 'UPDATE ' . TABLE_MES_DIALOGS . '
                    SET
                        last_update = @time, state = @state
                    WHERE
                        user_id = @user_id AND rec_id = @rec_id
                    ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInt( '@user_id', $user_id );
            $cmd->SetInt( '@rec_id' , $rec_id  );
            $cmd->SetInt( '@time'   , $time );
            $cmd->SetInt( '@state'  , MesDialogs::calculate_state( $read, $in ));
            echo $cmd->GetQuery() . '<br>';
            $cmd->Execute();
        }

        public static function get_dialog_ts( $user_id, $rec_id )
        {
            $sql = 'SELECT
                        last_update
                    FROM '
                        . TABLE_MES_DIALOGS . '
                    WHERE
                        user_id = @user_id AND rec_id = @rec_id ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInt( '@user_id', $user_id );
            $cmd->SetInt( '@rec_id' , $rec_id  );
            $ds = $cmd->Execute();
            $ds->Next();
            $ts = $ds->GetInteger( 'last_update' );
            return  $ts;
        }

        public static function calculate_state( $read, $in )
        {
            return ( $in && !$read ) ? 4 : 0;
        }

        //устанавливает статус диалога в нашей бд
        public static function set_state( $dialogs_id, $state, $change_time = 1 )
        {
            $time = $change_time ? ',last_update=@now' : '';
            $now = time();
            $dialogs_id = explode( ',', $dialogs_id );
            foreach( $dialogs_id as $dialog ) {
                $sql = 'UPDATE '
                            . TABLE_MES_DIALOGS .
                       ' SET
                            state=@state
                            ' . $time . '
                        WHERE
                            id=@dialog_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
                $cmd->SetInt( '@dialog_id', $dialog );
                $cmd->SetInt( '@state', $state );
                $cmd->SetInt( '@now',   $now );
//                echo $cmd->GetQuery() . '<br>';
                $cmd->Execute();
            }
        }

        public static function get_state( $dialog_id )
        {
            $sql = 'SELECT state FROM ' . TABLE_MES_DIALOGS .
                    ' WHERE  id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInt( '@dialog_id', $dialog_id );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger( 'state' );
        }

        //поиск по именам
        public static function search_dialogs( $user_id, $search )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                'access_token'  =>  $access_token,
                'q'             =>  $search,
                'fields'        =>  'photo,online,counters',
            );


            $res = VkHelper::api_request( 'messages.searchDialogs', $params, 0 );
            $result = array();
            foreach( $res as $user ) {
                if ( $user->type !='profile' )
                    continue;
                $result[] = array(
                    'userId'    =>  $user->uid,
                    'ava'       =>  $user->photo,
                    'name'      =>  $user->first_name . ' ' . $user->last_name,
                    'online'    =>  $user->online,
                    'dialog_id' =>  MesDialogs::get_dialog_id( $user_id, $user->uid )
                );
            }
            return $result;
        }

        //queue
        public static function add_message_to_queue( $user_id, $dialog_id, $text )
        {
            $now = time();
            $text_id = MesDialogs::add_text( $text);

            $sql = 'INSERT INTO '
                . TABLE_MES_QUEUES . ' ( id, created_time, user_id, dialog_id )
                    VALUES
                        ( @id, @created_time, @user_id, @dialog_id )';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@id',    $text_id );
            $cmd->SetInteger( '@dialog_id',    $dialog_id );
            $cmd->SetInteger( '@user_id',      $user_id );
            $cmd->SetInteger( '@created_time', $now );
            $cmd->Execute();
        }

        public static function add_text( $text, $out = true, $mid = null, $read = null )
        {
            $sql = 'INSERT INTO '
                . TABLE_MES_TEXTS . ' ( text, out, mid, read )
                    VALUES
                        ( @text, @out, @mid, @read )
                    RETURNING id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@mid', $mid );
            $cmd->SetBoolean( '@read', $read ? 'true' : 'false' );
            $cmd->SetString  ( '@text', $text );
            $cmd->SetBoolean ( '@out', $out ? 'true' : 'false' );
            $ds = $cmd->Execute();
            $ds->Next();
            return  $ds->GetInteger('id');
        }

        //queue
        public static function mark_message_as_sent( $message_id )
        {
            $now = time();
            $sql = 'UPDATE '
                . TABLE_MES_QUEUES . ' SET sent_time=@sent_time, sent=TRUE
                    WHERE id=@message_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@message_id', $message_id );
            $cmd->SetInteger( '@sent_time',  $now );
            $cmd->Execute();
        }

        //проверяет наличие завявок на добавление в друзья, конвертит их в диалоги
        public static function check_friend_requests( $user_id )
        {
            $requests = MesDialogs::get_friend_requests( $user_id );

            if ( $requests == 'no access_token' )
                return false;
            $group_id = MesGroups::get_groups_by_type( $user_id, 1 );

            $group_id = $group_id[0];
            if ( !$group_id ) {
                $group_id =  MesGroups::setGroup( '', 'friends', '' );
                MesGroups::set_group_type( $group_id, 1 );
                MesGroups::implement_group( $group_id, $user_id );
            }
            $ids = MesGroups::get_group_dialogs( $user_id, $group_id, 200 );
            foreach( $requests as $request ) {
                $dialog_id = MesDialogs::addDialog( $user_id, $request->uid, time(), 0, '' );

                if ( !$dialog_id )
                    $dialog_id = MesDialogs::get_dialog_id( $user_id, $request->uid );
//                if  ( $key !== false )
//                    unset( $ids[$key] );
                MesGroups::implement_entry( $group_id, $dialog_id, $user_id );
            }
            $ids = implode( ',', $ids );
            $res = StatUsers::get_friendship_state( $user_id, $ids );
            return;
        }

        //
        public static function get_friend_requests( $user_id )
        {
            $access_token = StatUsers::get_access_token( $user_id );
            if ( !$access_token )
                return 'no access_token';
            $params = array(
                            'access_token'  =>  $access_token,
                            'need_messages' =>  1,
                            'count'         =>  1000,
                            'out'           =>  0,
                            'sort'          =>  0
            );
            $requests = array();
            $offset = 0;
            while (1) {
                $res = VkHelper::api_request( 'friends.getRequests', $params, 0 );
                if ( isset ( $res->error ))
                    break;
                $requests = array_merge( $requests, $res );
                if ( count( $res ) < 1 || count( $res ) < 1000 )
                    break;
                $offset += 1000;
                $params['offset'] = $offset;
            }
            return $requests;
        }

        public static function log_activity( $dialog_id, $queued )
        {
            $now = time();
            $queued = $queued ? 'true' : 'false';
            $sql = 'INSERT INTO '
                        . TABLE_MES_ACTIVITY_LOG . '(dialog_id, activity_time, queued)
                    VALUES(@dialog_id, @activity_time, @queued)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger ( '@dialog_id',     $dialog_id );
            $cmd->SetInteger ( '@activity_time', $now );
            $cmd->SetBoolean( '@queued',         $queued );
            $cmd->Execute();
        }

        //templates
        public static function add_template( $user_id, $text, $groups )
        {
            $groups = '{' . $groups . '}';
            $sql = 'INSERT INTO ' . TABLE_MES_DIALOG_TEMPLATES . '
                        ( text, groups, user_id, created_at )
                    VALUES
                        ( @text, @groups, @user_id, @created_at )
                    RETURNING id
            ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger ( '@user_id', $user_id );
            $cmd->SetInteger ( '@created_at', time());
            $cmd->SetString  ( '@text', $text );
            $cmd->SetString  ( '@groups', $groups );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger( 'id');
        }

        //templates
        public static function edit_template( $user_id, $tmpl_id, $text, $group_ids )
        {
            $sql = 'UPDATE '
                        . TABLE_MES_DIALOG_TEMPLATES . '
                    SET
                        text=@text,
                        groups = @group_ids
                    WHERE
                        id=@tmpl_id
                        and user_id = @user_id
                    RETURNING id
            ';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString ( '@text'   , $text );
            $cmd->SetString ( '@group_ids'   , "{" . $group_ids . "}" );
            $cmd->SetInteger( '@tmpl_id', $tmpl_id );
            $cmd->SetInteger ( '@user_id',  $user_id );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger( 'id');
        }

        //templates
        public static function get_templates( $user_id, $group_id = 0, $offset = 0, $limit = 20 ) {
            $ad = '';
            if ( $group_id )
                $add = ' AND @group_id = any(groups) ';
            $sql = 'SELECT *
                    FROM ' . TABLE_MES_DIALOG_TEMPLATES . '
                    WHERE user_id = @user_id '. $add . '
                    ORDER BY created_at DESC
                    OFFSET  @offset
                    LIMIT   @limit
                    ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger ( '@user_id',  $user_id );
            $cmd->SetInteger ( '@group_id', $group_id );
            $cmd->SetInteger ( '@offset',   $offset );
            $cmd->SetInteger ( '@limit',    $limit );
            $ds = $cmd->Execute();
            $res  = array();
            while( $ds->Next()) {
                $res[] = array(
                    'text'      =>  $ds->GetValue( 'text' ),
                    'tmpl_id'   =>  $ds->Getinteger( 'id' ),
                    'groups'    =>  explode(',', trim( $ds->GetString( 'groups' ),'{}' ))
                );
            }
            return $res;

        }

        //templates
        public static function search_template( $search, $group_id )
        {
            $text   = pg_escape_string( $search );
            $search = $search ? " text ILIKE '%" . $text . "%' " : ' 1 = 1' ;

            $sql = 'SELECT
                        text, id
                    FROM '
                        . TABLE_MES_DIALOG_TEMPLATES . '
                    WHERE '
                        . $search .
                        ' AND @group_id = ANY(groups)';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString ( '@group_id', $group_id );
            $ds = $cmd->Execute();
            $res = array();
            while($ds->Next()) {
                $res[] = array(
                                'text'      =>  $ds->GetValue('text'),
                                'tmpl_id'   =>  $ds->GetValue('id'));
            }
            return $res;
        }

        //templates
        public static function del_template( $tmpl_id )
        {
            $sql = 'DELETE FROM '
                        . TABLE_MES_DIALOG_TEMPLATES . '
                    WHERE
                          id=@id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString( '@id', $tmpl_id );

            return $cmd->ExecuteNonQuery() ? true : false;
        }

        public static function delete_dialog( $user_id, $rec_id )
        {
            $dialog_id = MesDialogs::get_dialog_id( $user_id, $rec_id );
            $sql = 'DELETE FROM ' . TABLE_MES_DIALOGS . ' WHERE id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->Execute();

            $sql = 'DELETE FROM ' . TABLE_MES_GROUP_DIALOG_REL . ' WHERE dialog_id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->Execute();
        }

        //
        public static function check_new_messages( $im_users, $count = 0 )
        {
            if ( !is_array( $im_users ))
                $im_users = array( $im_users );

            foreach( $im_users as $user ) {
                $dialogs = MesDialogs::get_all_dialogs( $user, $count );
                if ( $dialogs == 'no access_token' )
                    return false;
                if ( $user == 13049517) {
                    file_put_contents( '1.txt', json_encode($dialogs) );
                    die();
                }
                $default_group = MesGroups::get_unlist_dialogs_group( $user);
                if ( !$dialogs )
                    continue;
                $i = 0;
                foreach( $dialogs as $dialog ) {
                    if ( $count && $i++ > $count )
                        break;
                    if ( isset( $dialog->chat_id ))
                        continue;

                    //highlighted
                    $dialog_id = MesDialogs::get_dialog_id( $user, $dialog->uid );
                    $group_ids = MesGroups::get_dialog_group( $dialog_id );
                    if ( empty( $group_ids )) {
                        MesGroups::implement_entry( $default_group, $dialog_id, $user );
                        $group_ids = array( $default_group );
                    }
                    $old_ts    = MesDialogs::get_dialog_ts( $user, $dialog->uid );
                    $last_clear_time = MesGroups::get_last_clear_time( $group_ids[0], $user );
                    $act = '';
                    if ( $dialog->read_state ) {
                        self::set_state( $dialog_id, 0, 0 );
                        MesDialogs::set_text_read( $dialog_id );
                    }
                    if ( !$dialog->read_state && !$dialog->out && $old_ts != $dialog->date && $last_clear_time < $dialog->date )
                        $act = 'add';
                    elseif( $dialog->read_state && !$dialog->out || $dialog->out )
                        $act = 'del';

                    if ( $act )
                        MesGroups::update_highlighted_list( $group_ids, $user, $act, $dialog_id );
                    //обновление статуса
                    if ( $old_ts == $dialog->date )
                        continue;

                    //если в диалоге появились более поздние линии, сохраняем их и время
                    self::save_last_line( $dialog_id, $dialog->body, !$dialog->out, $dialog->mid, $dialog->read_state );
                    MesDialogs::set_dialog_ts( $user, $dialog->uid, $dialog->date, !$dialog->out, $dialog->read_state );
                    MesGroups::update_highlighted_list( $group_ids, $user, $act, $dialog_id );
                }
            }
        }

        public static function save_last_line( $dialog_id, $text, $out, $mid, $read )
        {
            $text_id = self::add_text( $text, $out, $mid, $read );
            $sql = 'UPDATE ' . TABLE_MES_DIALOGS . '
                    SET
                        text_id=@text_id
                    WHERE
                        id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@text_id', $text_id );
            $cmd->SetInteger( '@dialog_id', $dialog_id );

            $cmd->Execute();
        }

        // для быстрой подгрузки старые заипси диалогов(последее сообщение) сохраняются у нас, выдаются без обращений к контакту
        public static function get_dialogs_from_db( $user_id, $res_ids )
        {
            $res_ids = implode( ',', $res_ids );
            $sql = 'SELECT
                        a.id, rec_id,state,text,out,mid,last_update, read
                    FROM '
                        . TABLE_MES_DIALOGS . ' AS a
                    JOIN '
                        . TABLE_MES_TEXTS . ' as b on( a.text_id=b.id )
                    WHERE
                            user_id = @user_id
                        AND rec_id IN (' . $res_ids . ')
                    ORDER BY last_update DESC';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[] = (object) array(
                    'mid'           =>  $ds->GetInteger('mid'),
                    'date'          =>  $ds->GetInteger('last_update'),
                    'out'           =>  !$ds->GetBoolean('out'),
                    'uid'           =>  $ds->GetInteger('rec_id'),
                    'read_state'    =>  $ds->GetBoolean('read') ? 1 : 0,
                    'mid'           =>  $ds->GetInteger('mid'),
                    'title'         =>  $ds->GetString( 'text'),
                    'body'          =>  $ds->GetString( 'text')
                );
            }
            return $res;
        }

        public function get_dialogs_list_from_db( $user_id ) {
            $sql = 'SELECT
                        id, rec_id
                    FROM '
                        . TABLE_MES_DIALOGS . '
                    WHERE user_id=@user_id
            ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@user_id', $user_id );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[$ds->GetInteger('id')] = array(
                    $ds->GetInteger('rec_id')
                );
            }
            return $res;
        }

        public static function set_text_read( $dialog_id )
        {
            $sql = 'SELECT text_id FROM '
                        . TABLE_MES_DIALOGS . '
                    WHERE id = @dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $ds = $cmd->Execute();
            $ds->Next();
            $text_id = $ds->GetInteger( 'text_id' );
            $sql = 'UPDATE '
                        . TABLE_MES_TEXTS . '
                    SET read = true
                    WHERE id = @text_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@text_id', $text_id );
            $cmd->Execute();
        }
    }
?>
