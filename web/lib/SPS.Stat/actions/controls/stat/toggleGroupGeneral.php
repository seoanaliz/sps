<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    new stat_tables();
    class toggleGroupGeneral {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );

            $group_id  =  Request::getInteger( 'groupId' );
            $user_id   =  AuthVkontakte::IsAuth();
            if( !$group_id ) {
                die( ObjectHelper::ToJSON(array( 'response' => false )));
            }

            if( StatAccessUtility::CanEditGlobalGroups( $user_id, Group::STAT_GROUP)) {
                $group = GroupFactory::GetById($group_id);
                if( !empty( $group )) {
                    if( $group->type == GroupsUtility::Group_Global) {
                        $group->type = GroupsUtility::Group_Private;
                        new GroupUser($group_id, $user_id, Group::STAT_GROUP);
                        GroupUserFactory::Add($group);
                    } else {
                        $group->type = GroupsUtility::Group_Global;
                        GroupUserFactory::DeleteByMask( array(
                            'groupId'       =>  $group_id,
                            'vkId'          =>  $user_id,
                            'sourceTyoe'    =>  Group::STAT_GROUP
                        ));
                    }
                    die( ObjectHelper::ToJSON(array( 'response' => true )));
                }
            };
            die( ObjectHelper::ToJSON(array( 'response' => false )));

        }
    }
?>