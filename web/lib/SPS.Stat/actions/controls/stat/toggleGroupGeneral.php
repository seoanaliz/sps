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
            $group_id  =  Request::getInteger( 'groupId' );
            $user_id   =  AuthVkontakte::IsAuth();
            if (!$group_id) {
                die( ObjectHelper::ToJSON(array( 'response' => false )));
            }

            if( StatAccessUtility::CanManageGlobalGroups( $user_id, Group::STAT_GROUP)) {
                $group = GroupFactory::GetById($group_id);
                if (!empty( $group )) {
                    if ($group->type == GroupsUtility::Group_Global) {
                        $group->type = GroupsUtility::Group_Private;
                        GroupUserFactory::Add( new GroupUser($group_id, $user_id, Group::STAT_GROUP));
                    } else {
                        $group->type = GroupsUtility::Group_Global;
                        GroupUserFactory::DeleteByMask( array(
                            'groupId'       =>  $group_id,
                            'vkId'          =>  $user_id,
                            'sourceType'    =>  Group::STAT_GROUP
                        ));
                    }
                    GroupFactory::Update($group);
                    die(ObjectHelper::ToJSON(array( 'response' => true )));
                }
            };
            die(ObjectHelper::ToJSON(array( 'response' => false )));
        }
    }
?>