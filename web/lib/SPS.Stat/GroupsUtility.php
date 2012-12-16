<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/

    class GroupsUtility
    {
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

        //возвращает дефолтню группу для этого типа групп. Нет - создаст
        public static function get_default_group( $user_id, $groupe_sourse ) {

            $default_group = GroupFactory::Get( array( '_created_by' => $user_id, 'source' => $groupe_sourse, 'type' => 2 ));
            if( empty( $default_group )) {
                $default_group = new Group;
                $default_group->created_by  =   $user_id;
                $default_group->name        =   self::DEFAULT_GROUPE_NAME;
                $default_group->source      =   $groupe_sourse;
                $default_group->status      =   1;
                $default_group->type        =   2;
                $default_group->users_ids   =   array( $user_id );
                GroupFactory::Add( $default_group, array( BaseFactory::WithReturningKeys => true ));

                if ( !$default_group->group_id )
                    return false;
            } else
                $default_group = reset( $default_group );
            return $default_group;
        }

        //проверяет уникальность предлагаемого имени группы для данного типа групп данного пользователя
        public static function check_name( $user_id, $group_source, $group_name )
        {
            $check = GroupFactory::GetOne( array( 'name' => $group_name, 'created_by' => $user_id, 'source' => $group_source ));
            if ( !$check )
                return true;
            return false;
        }

        //удаляет группу - точнее, все упоминания группы. она сама меняет статус
        public static function delete_group( Group $group, Group $default_group )
        {
            //todo определение типа группы (бартер, мессагер...)
            //ищем все привязанные к групе записи, удаляем отметку этой группы
//            $entries = BarterEventFactory::Get( array( '_groups_ids' => array( $group->group_id )), array(), 'tst');
//            foreach( $entries as $entry ) {
//                $key = array_search( $group->group_id, $entry->groups_ids );
//                if ( $key === false || $key === null )
//                    continue;
//                unset( $entry->groups_ids[ $key ]);
//                //если отметок о группах не осталось, ставим дефолтную
//                if ( count($entry->groups_ids == 0))
//                    $entry->groups_ids = array( $default_group->group_id );
//            }
//            BarterEventFactory::UpdateRange( $entries, null, 'tst');
//
//            $group->users_ids   =   array(0);
            $group->status      =   7;
            GroupFactory::Update( $group );

        }

        //формирует отчет для групп. Если указан user_id, разделяет созданные им группы и нет
        public static function form_response( $groups, $user_id, $group_source )
        {
        //todo place
            if( !is_array( $groups ))
                $groups = array( $groups );
            $res = array();
            $i = 1;
            foreach( $groups as $group ) {
                $field = ( $group->created_by == $user_id ) ? 'user_lists' : 'shared_lists';
                $field = ( $group->created_by == $user_id && $group->type == 2 ) ? 'default_group' : $field;
                $res[$field][] = array(
                    'group_id'  =>  $group->group_id,
                    'type'      =>  $group->type,
                    'name'      =>  ( $field == 'default_group' ) ? 'Не в списке' : $group->name,
                    'place'     =>  ( $field == 'default_group' ) ? 0 : $i++
                );
            }
            if( !isset( $res['user_lists'] )) $res['user_lists'] = array();
            if( !isset( $res['shared_lists'] )) $res['shared_lists'] = array();
            if( !isset( $res['default_group'] )) $res['default_group'] = array( GroupsUtility::get_default_group( $user_id, $group_source ));
            return $res;
        }

        //проверяет, является ли юзер автором группы
        public static function is_author( $group_id, $user_id)
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
                if ( $group->type == 2 )
                    continue;
                $group->users_ids = array_unique( array_merge( $group->users_ids, $rec_ids ));
            }
        }
    }
?>
