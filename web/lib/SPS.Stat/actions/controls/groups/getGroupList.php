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
            error_reporting( 0 );
            $user_id    =   AuthVkontakte::IsAuth();
            $type       =   ucfirst( Request::getString( 'type' ));
            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array ))
                $type    = 'Stat';

            if( $type = 'Stat') {
                $global_groups = GroupFactory::Get(array( 'type' => GroupsUtility::Group_Global, 'source'=>Group::STAT_GROUP));
                $user_groups = array();
                $shared_groups = array();
                if( $user_id ) {
                    $groupsUsers = GroupUserFactory::Get(array(
                        'vkId'          => $user_id,
                        'sourceType'    => Group::STAT_GROUP)
                    );
                    $groups_ids = array();
                    foreach( $groupsUsers as $GroupUser ) {
                        $groups_ids[] = $GroupUser->groupId;
                    }

                    if( !empty($groups_ids) ) {
                        $user_groups = GroupFactory::Get(array(
                            '_group_id' =>  $groups_ids
                        ));
                    }
                }

                $res['lists'] = array(
                    'global_list' => $global_groups,
                    'private_list'=> $user_groups,
                    'shared_list' => $shared_groups,
                );
            }


            if ( $type == 'Barter' ) {
                $source = 1;
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
    }