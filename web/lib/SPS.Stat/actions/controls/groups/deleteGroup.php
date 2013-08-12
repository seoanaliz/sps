<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

    //удалить группу, обычную - для юзера, general - для всех юзеров
    class deleteGroup {

        /**
         * Entry Point
         */

        public function Execute() {
            new stat_tables();
            $user_id  = AuthVkontakte::IsAuth();
            $group_id = Request::getString ( 'groupId' );
            $general  = Request::getInteger ( 'general' );
            $type     = ucfirst( Request::getString( 'type' ));

            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array ))
                $type    = 'Stat';

            $m_class    = $type . 'Groups';
            $general = $general ? $general : 0;
            if ( !$group_id || !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }

            if( $type == 'Barter' ) {
                $source = 1;
                $default_group = GroupsUtility::get_default_group( $user_id, $source  );
                $group_ids = explode( ',', $group_id  );
                foreach( $group_ids as $group_id ) {

                    $group = GroupFactory::GetOne( array( 'group_id' => $group_id));
                    if ( !$group )
                        continue;

                    if ( $group->group_id === $default_group->group_id ) {
                        //дефолтные группы удалять нельзя
                        continue;
                    } elseif ( $group && $group->created_by == $user_id ) {
                        //жесткое удаление группы, только создавший
                        GroupsUtility::delete_group( $group, $default_group );
                    }
                      else {
                          //отписываем человека от группы
                          GroupsUtility::dismiss_from_group( $group, $user_id );
                    }
                }
                die( ObjectHelper::ToJSON( array( 'response' => true )));
            } elseif ( $type == 'Stat' ) {
                $res   = false;
                $group = GroupFactory::GetOne( array( 'group_id' => $group_id));
                if( empty( $group )) {
                    die( ObjectHelper::ToJSON( array( 'response' => false )));
                }

                $is_global_can_delete = $group->type == GroupsUtility::Group_Global &&
                    StatAccessUtility::CanManageGlobalGroups( $user_id, Group::STAT_GROUP );

                $is_private_can_delete = $group->type == GroupsUtility::Group_Private &&
                    StatAccessUtility::HasAccessToPrivateGroups( $user_id, Group::STAT_GROUP );

                if( $is_global_can_delete) {
                    $user_id = GroupsUtility::Fake_User_ID_Global;
                }

                if ( $is_global_can_delete || $is_private_can_delete ) {
                    $group->status = 2;

                    GroupEntryFactory::DeleteByMask( array(
                        'groupId'       =>  $group->group_id,
                        'sourceType'    =>  Group::STAT_GROUP
                    ));

                    GroupUserFactory::DeleteByMask( array(
                        'groupId'       =>  $group->group_id,
                        'sourceType'    =>  Group::STAT_GROUP,
                        'vkId'          =>  $user_id
                    ));
                    $NewGroupUsers = GroupsUtility::sort_groups_users( $user_id, Group::STAT_GROUP );
                    GroupUserFactory::DeleteByMask( array(
                        'sourceType'    =>  Group::STAT_GROUP,
                        'vkId'          =>  $user_id
                    ));
                    GroupUserFactory::AddRange($NewGroupUsers);
                    GroupFactory::Update($group);
                    $res = true;
                }

                die(ObjectHelper::ToJSON( array( 'response' => $res )));
            }

            if ( statUsers::is_Sadmin( $user_id )) {
                $res = $m_class::delete_group( $group_id );
            }
//            elseif ( !$general ) {
//                $res = $m_class::extricate_group( $group_id, $user_id );
//
//            }
            else {
                die( ObjectHelper::ToJSON(array('response' => false)));

            }

            if ( $res ) {
                die(  ObjectHelper::ToJSON(array('response' => true)));
            }

            die( ObjectHelper::ToJSON(array('response' => false)));
        }

        private function remove_inLists_state( $group_id ) {
        }
    }
?>