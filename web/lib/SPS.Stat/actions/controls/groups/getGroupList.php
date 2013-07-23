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
            $res = array( 'success' => false );
            $user_id    =   AuthVkontakte::IsAuth();
            $type       =   ucfirst( Request::getString( 'type' ));
            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array )) {
                $type    = 'Stat';
            }
            if ( $type == 'Stat') {
                $global_groups = GroupFactory::Get(
                    array(
                        'type'  =>  GroupsUtility::Group_Global,
                        'source'=>  Group::STAT_GROUP
                    ));
                GroupsUtility::set_default_order($global_groups);
                $global_groups = $this->get_global_list( $global_groups, Group::STAT_GROUP );

                $user_groups = array();
                $shared_groups = array();
                if( $user_id ) {
                    $user_groups_uns  = array();
                    $groupsUsers = GroupUserFactory::Get(array(
                        'vkId'          => $user_id,
                        'sourceType'    => Group::STAT_GROUP)
                    ,array(
                        'orderBy'   => 'place'
                    ));
                    $groups_ids = array();
                    foreach( $groupsUsers as $GroupUser ) {
                        $groups_ids[] = $GroupUser->groupId;
                    }

                    if( !empty($groups_ids) ) {
                        $user_groups_uns = GroupFactory::Get(array(
                            '_group_id' =>  $groups_ids
                        ));
                    }

                    foreach( $groups_ids as $group_id ) {
                        if( isset( $user_groups_uns[$group_id])) {
                            $user_groups[] = array(
                                'id'    =>  $user_groups_uns[$group_id]->group_id,
                                'name'  =>  $user_groups_uns[$group_id]->name
                            );
                        }
                    }

                    $this->check_groups_const($groupsUsers, $user_groups_uns, $user_id, Group::STAT_GROUP );
                }

                $res['data'] = array(
                    'global' => $global_groups,
                    'private'=> $user_groups,
                    'shared' => $shared_groups,
                );
                $res['success'] = true;
                die( ObjectHelper::ToJSON( $res ));
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

        private function get_global_list( $global_groups, $source )
        {

            $global_groupUser = GroupUserFactory::Get( array(
                'vkId'          =>  GroupsUtility::Fake_User_ID_Global,
                'sourceType'    =>  $source
                )
                , array( 'orderBy'   =>  'place')
            );

            $result = array();
            foreach( $global_groupUser as $ggu ) {
                if( isset( $global_groups[$ggu->groupId])) {
                    $result[] = array(
                        'id'    =>  $global_groups[$ggu->groupId]->group_id,
                        'name'  =>  $global_groups[$ggu->groupId]->name
                    );
                } else {
                    GroupUserFactory::DeleteByMask( array(
                        'vkId'          =>  GroupsUtility::Fake_User_ID_Global,
                        'groupId'       =>  $ggu->groupId,
                        'sourceType'    =>  $source
                    ));
                }
            }
            return $result;
        }

        private function check_groups_const( $groupUsers, $groups, $user_id, $source ) {
            foreach( $groupUsers as $gu ) {
                if( !isset( $groups[$gu->groupId])) {
                    GroupUserFactory::DeleteByMask( array(
                        'vkId'          =>  $user_id,
                        'groupId'       =>  $gu->groupId,
                        'sourceType'    =>  $source
                    ));
                }
            }
        }
    }