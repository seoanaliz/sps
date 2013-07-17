<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/

    class GroupsUtility
    {

        const Group_Type_Default = 1;
        const Group_Shared  = 2;
        const Group_Shared_Special = 3;
        const Group_Private = 4;
        const Group_Global  = 5;

        const Group_Id_Special_All = 'all';
        const Group_Id_Special_All_Not = 'all_not_listed';

        const Fake_User_ID_Global = -1;

        public static $special_group_ids = array(
            self::Group_Id_Special_All      => 'Все',
            self::Group_Id_Special_All_Not  => 'Не в группе',
        );

        public static  $barter_watchers = array(
            '670456',
            '106175502',
            '196506553',
        );

        //прикрепить запись к группе
        public static function implement_to_group( $objects, $group_id, $only_one_group = 0 ) {
            foreach( $objects as $object ) {
                if ( $only_one_group )
                    $object->groups_ids = array( $group_id );
                elseif( !in_array( $group_id, $object->groups_ids ))
                    $object->groups_ids[] = $group_id;
            }
        }

        //открепить запись от группы
        public static function extricate_from_group( $objects, $group_id, $default_group_id, $only_one_group = 0 ) {
            if ( !is_array( $objects ))
                $objects = array( $objects );

            foreach( $objects as $object ) {
                if ( $only_one_group )
                    $object->groups_ids = array( $default_group_id );
                else{
                    $key = array_search( $group_id, $object->groups_ids );
                    if ( $key === false || $key === null )
                        continue;
                    unset( $object->groups_ids[$key] );
                    if ( empty( $object->groups_ids ))
                        $object->groups_ids = array( $default_group_id );
                }
            }

        }

        //подписать человека на группу
        public static function assign_to_group( Group $group, $user_id )
        {
            if( !in_array( $user_id, $group->users_ids )) {
                $group->users_ids[] = $user_id;
                GroupFactory::Update( $group );
            }
        }

        //"отписать" человека от группы
        public static function dismiss_from_group( $groups, $user_id ) {
            //todo general
            if ( !is_array( $groups ))
                $groups = array( $groups );

            foreach( $groups as $group ) {
                $key = array_search( $user_id, $group->users_ids );
                if ( $key === false || $key === null )
                    continue;
                unset( $group->users_ids[$key] );
                if ( empty( $group->users_ids ))
                    $group->groups_ids = array( 0 );
            }
            GroupFactory::UpdateRange( $groups );
        }

        //возвращает дефолтную группу для этого типа групп. Нет - создаст
        public static function get_default_group( $user_id, $groupe_sourse ) {
            $default_group = GroupFactory::Get( array( '_created_by' => $user_id, 'source' => $groupe_sourse, 'type' => 2 ));
            if( empty( $default_group )) {
                $users = self::$barter_watchers;
                $users[] = $user_id;
                $default_group = new Group;
                $default_group->created_by  =   $user_id;
                $default_group->name        =   'default_group';
                $default_group->source      =   $groupe_sourse;
                $default_group->status      =   1;
                $default_group->type        =   2;
                $default_group->users_ids   =   $users;
                GroupFactory::Add( $default_group, array( BaseFactory::WithReturningKeys => true ));

                if ( !$default_group->group_id )
                    return false;
            } else
                $default_group = reset( $default_group );
            return $default_group;
        }

        public static function get_all_user_groups( $user_id, $groupe_sourse )
        {
            $search = array( '_users_ids_in_array' => array( $user_id ), 'source'=> $groupe_sourse );
            $groups = GroupFactory::Get( $search );
            $result = array();

            foreach( $groups as $group ) {
                $result[] = $group->group_id;
            }
            return $result;
        }

        //проверяет уникальность предлагаемого имени группы для данного типа групп данного пользователя
        public static function check_name( $user_id, $group_source, $group_name )
        {
            $check = GroupFactory::Get( array(
                    'name'       =>     $group_name
                ,   'created_by' =>     $user_id
                ,   'source'     =>     $group_source
                ,   '_statusNE'  =>     2 ));
            if ( !$check )
                return true;
            return false;
        }

        //удаляет группу - точнее, все упоминания группы. она сама меняет статус
        public static function delete_group( Group $group )
        {
            //удаляем эвенты группы
            $object = new BarterEvent();
            $object->status = 7;
            $search = array( '_groups_ids' => array( $group->group_id ));
            BarterEventFactory::UpdateByMask( $object, array( 'status'), $search );

            $group->users_ids   =   array(0);
            $group->status      =   7;
            GroupFactory::Update( $group );

        }

        //формирует отчет для групп. Если указан user_id, разделяет созданные им группы и нет
        public static function form_response(  $groups, $user_id, $group_source )
        {
        //todo place
            $user_shared_groups = array();
            if( !is_array( $groups ))
                $groups = array( $groups );
            $res = array();
            $i = 1;

            foreach( $groups as $group ) {
                /** @var $group Group*/
                if( $group->created_by != $user_id && $group->type != self::Group_Shared_Special ) {
                    $user_shared_groups[$group->created_by][] = $group->group_id;
                } else {
                    $tmp = array(
                        'group_id'  =>  $group->group_id,
                        'type'      =>  $group->type,
                        'name'      =>  $group->type == 2 ? 'Мой первый список' : $group->name,
                        'place'     =>  $group->type == 2 ? 0 : $i++
                    );
                    if( $group->type == 2 ) {
                        if( !is_array( $res['user_lists']))
                            $res['user_lists'] = array();
                        array_unshift( $res['user_lists'], $tmp );
                    } else {
                        $res['user_lists'][] = $tmp;
                    }
                }
            }
            $users_list = array_keys( $user_shared_groups);
            $users_info = StatUsers::get_vk_user_info( $users_list );
            foreach( $user_shared_groups as $sharer_id => $sharer_groups ) {
                $res['shared_lists'][] = array(
                    'group_id'  =>  implode( ',', $sharer_groups ),
                    'type'      =>  1,
                    'name'      =>  $users_info[$sharer_id]['name'],
                    'place'     =>  $i++
                );
            }

            if( !isset( $res['user_lists'] ))   $res['user_lists'] = array();
            if( !isset( $res['shared_lists'] )) $res['shared_lists'] = array();
            if( !isset( $res['default_list'] )) $res['default_list'] = array();
            return $res;
        }

        //проверяет, является ли юзер автором группы
        public static function is_author( $group_id, $user_id )
        {
            $group = GroupFactory::GetOne( array( 'group_id' => $group_id, 'created_by'=> $user_id ));
            if ( $group )
                return $group;
            return false;
        }

        public static function has_access_to_group( $group_id, $user_id )
        {
            $group = GroupFactory::GetOne( array( 'group_id' => $group_id, '_users_ids'=> array( $user_id )));
            if ( $group )
                return $group;
            return false;
        }

        public static function share_groups( $groups, $rec_ids )
        {
            foreach( $groups as $group ) {
//                if ( $group->type == 2 )
//                    continue;
                $group->users_ids = array_unique( array_merge( $group->users_ids, $rec_ids ));
            }
        }

        public static function get_next_index_groupUser( $user_id, $source ) {
            $count = GroupUserFactory::Count( array(
                'vkId'      =>  $user_id,
                'sourceType'=>  $source
            ));
            return $count + 1;
        }

        //проверяетзадан ли порядкок для общих категорий. нет - делает его
        public static function set_default_order( $global_groups = false ) {
            $i = 0;
            if( !$global_groups) {
                $global_groups = GroupFactory::Get( array(
                    'type'      =>  GroupsUtility::Group_Global,
                    'source'    =>  Group::STAT_GROUP
                ));
            }
            $check = GroupUserFactory::Get( array(
                'groupId'   =>  current($global_groups)->group_id,
                'vkId'    =>  GroupsUtility::Fake_User_ID_Global
            ));
            if ( !empty($check)) {
                return;
            }
            $global_groupUser = array();
            foreach( $global_groups as $global_group ) {
                $tmp = new GroupUser($global_group->group_id, GroupsUtility::Fake_User_ID_Global, Group::STAT_GROUP);
                $tmp->place = ++$i;
                $global_groupUser[] = $tmp;
            }

            GroupUserFactory::AddRange( $global_groupUser);
        }
    }
?>
