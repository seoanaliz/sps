<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
    * addPrice Action
    * @package    SPS
    * @subpackage Stat
    */

    class getGroupList
    {

    /**
    * Entry Point
    */
        public function Execute()
        {
            $user_id    =   AuthVkontakte::IsAuth();
            $type       =   ucfirst( Request::getString( 'type' ));
            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array )) {
                $type    = 'Stat';
            }
            if ( $type == 'Stat') {
                $global_groups = $this->get_global_list( Group::STAT_GROUP );
                GroupsUtility::set_default_order($global_groups);
                $user_groups = array();
                $shared_groups = array();
                if( $user_id ) {
                    $groupsUsers = GroupUserFactory::Get(array(
                        'vkId'          => $user_id,
                        'sourceType'    => Group::STAT_GROUP)
                    ,array(
                            'orderBy'   => 'place'
                        )
                    );
                    $groups_ids = array();
                    foreach( $groupsUsers as $GroupUser ) {
                        $groups_ids[$GroupUser->groupId] = $GroupUser->place;
                    }

                    if( !empty($groups_ids) ) {
                        $user_groups_uns = GroupFactory::Get(array(
                            '_group_id' =>  array_keys( $groups_ids )
                        ));
                        foreach( $groups_ids as $group_id => $place ) {
                            if( isset($user_groups_uns[$group_id])) {
                                $tmp = $user_groups_uns[$group_id];
                                $tmp->place = $place;
                                $user_groups[$group_id] = $tmp;
                            }
                        }
                    }
                }

                $res['lists'] = array(
                    'global_list' => $global_groups,
                    'private_list'=> $user_groups,
                    'shared_list' => $shared_groups,
                );
            }

            if ( $type == 'Barter' ) {
                GroupsUtility::get_default_group( $user_id, Group::BARTER_GROUP );
                $search = array(
                    'status' => 1,
                    'source' => Group::BARTER_GROUP,
                    '_users_ids' => array( $user_id )
                );

                $options = array( 'orderBy' => 'type' );
                $groups = GroupFactory::Get( $search, $options );
                die( ObjectHelper::ToJSON( array( 'response' => GroupsUtility::form_response( $groups, $user_id, Group::BARTER_GROUP ))));
            }
//            $res = $m_class::get_groups( $user_id );

            die( ObjectHelper::ToJSON( array( 'response' => $res )));
        }

        /** @var $groups Group[]*/
        public function form_stat_lists( $groups ) {
            $res = array();
            foreach($groups as $group ) {
                $res[] = array(
                    'group_id'  =>  $group->group_id ,
                    'general'   =>  $group->general,
                    'name'      =>  $group->name,
                    'comments'  =>  '',
                    'fave'      =>  $group->general,
                    'group_type'=>  $group->type,
                );
            }
            return $res;
        }

        private function get_global_list( $source )
        {
            $global_groups = GroupFactory::Get(
                array(
                    'type'  =>  GroupsUtility::Group_Global,
                    'source'=>  $source
                )
            );

            $global_groupUser = GroupUserFactory::Get( array(
                'vkId'          =>  GroupsUtility::Fake_User_ID_Global,
                'sourceType'    =>  $source
                )
            , array(
                    'orderBy'   =>  'place'
                )
            );

            $result = array();
            foreach( $global_groupUser as $ggu ) {
                if( isset ( $global_groups[$ggu->groupId] )) {
                    $tmp = $global_groups[$ggu->groupId];
                    $tmp->place = $ggu->place;
                    $result[$ggu->groupId] = $tmp;
                }
            }
            return $result;
        }
    }