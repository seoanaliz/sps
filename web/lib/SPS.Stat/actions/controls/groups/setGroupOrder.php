<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
    * addPrice Action
    * @package    SPS
    * @subpackage Stat
    */

    class setGroupOrder {

    /**
    * Entry Point
    */
        public function Execute() {
            $user_id    =   AuthVkontakte::IsAuth();
            $group_ids  =   Request::getString( 'groupIds' );
            $type       =   Request::getString ( 'type' );
            $type_array         = array( 'Stat', 'Mes', 'stat', 'mes');

            if (!$group_ids) {
                die( ObjectHelper::ToJSON( array( 'success' => false )));
            }
            if (!is_array( $group_ids )) {
                $group_ids = explode( ',', $group_ids);
            }

            if (!$type || !in_array( $type, $type_array, 1 ))
                $type = 'Stat';


            $group = GroupFactory::GetById( current( $group_ids ));
            if ( empty( $group )) {
                die( ObjectHelper::ToJSON( array( 'success' => false )));
            }
            if ($type == "Stat") {
                $list_type = $group->type;
                //проверяем права, подменяем юзера на фейкового для эдита глобальных категорий
                if( $list_type == GroupsUtility::Group_Global ) {
                    if( !StatAccessUtility::CanManageGlobalGroups( $user_id, Group::STAT_GROUP))
                        die( ObjectHelper::ToJSON( array( 'success' => false )));
                    GroupsUtility::set_default_order();
                    $user_id = GroupsUtility::Fake_User_ID_Global;
                } else {
                    if( !StatAccessUtility::CanEditGlobalGroups( $user_id, Group::STAT_GROUP)) {
                        die( ObjectHelper::ToJSON( array( 'success' => false )));
                    }
                }

                $GroupUsers = GroupUserFactory::Get( array(
                    'vkId'          =>  $user_id,
                    'sourceType'    =>  Group::STAT_GROUP
                ));

                //если количество не совпадает
                if( count( $GroupUsers ) != count( $group_ids )) {
                    die( ObjectHelper::ToJSON( array( 'success' => false )));
                }
                $GroupUsers = ArrayHelper::Collapse( $GroupUsers, 'groupId', false );

                $i = 0;
                $NewGroupUsers = array();
                foreach( $group_ids as $group_id ) {
                    if( isset( $GroupUsers[$group_id])) {
                        $GroupUsers[$group_id]->place = ++$i;
                        $NewGroupUsers[] = $GroupUsers[$group_id];
                    } else {
                        die( ObjectHelper::ToJSON( array( 'success' => false )));
                    }
                }

                GroupUserFactory::DeleteByMask( array(
                    'vkId'          =>  $user_id,
                    'sourceType'    =>  Group::STAT_GROUP
                ));

                $res = GroupUserFactory::AddRange( $NewGroupUsers );
                die( ObjectHelper::ToJSON( array( 'success' => $res )));
            }
            $m_class  = $type . 'Groups';
            if ( !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }

            die( ObjectHelper::ToJSON( array( 'success' => 'true' )));
        }
    }