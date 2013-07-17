<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
    * addPrice Action
    * @package    SPS
    * @subpackage Stat
    */

    class setGroupOrder
    {

    /**
    * Entry Point
    */
        public function Execute()
        {
            $user_id    =   AuthVkontakte::IsAuth();
            $group_id   =   Request::getInteger ( 'groupId' );
            $new_place  =   Request::getInteger ( 'index' );
            $type       =   Request::getString ( 'type' );
            $type_array         = array( 'Stat', 'Mes', 'stat', 'mes');

            if( !$group_id ) {
                die( ObjectHelper::ToJSON( array( 'response' => false )));
            }
            if ( !$type || !in_array( $type, $type_array, 1 ) )
                $type = 'Stat';

            $group = GroupFactory::GetById( $group_id );
            if ( empty( $group )) {
                die( ObjectHelper::ToJSON( array( 'response' => false )));
            }

            $list_type = $group->type;
            if( $type == "Stat") {
                {

                    //проверяем права, подменяем юзера на фейкового для эдита глобальных категорий
                    if( $list_type == GroupsUtility::Group_Global ) {
                        if( !StatAccessUtility::CanManageGlobalGroups( $user_id, Group::STAT_GROUP))
                            die( ObjectHelper::ToJSON( array( 'response' => false )));
                        GroupsUtility::set_default_order();
                        $user_id = GroupsUtility::Fake_User_ID_Global;
                    } else {
                        if( !StatAccessUtility::CanEditGlobalGroups( $user_id, Group::STAT_GROUP)) {
                            die( ObjectHelper::ToJSON( array( 'response' => false )));
                        }
                    }

                    $groupUsersForUpdate = GroupUserFactory::Get( array(
                       'vkId'       =>  $user_id,
                       'sourceType' =>  Group::STAT_GROUP
                    ));

                    if ( !empty( $groupUsersForUpdate )) {
                        $groupUserArray = $this->replace( $group_id, $new_place, $groupUsersForUpdate);
                    } else {
                        die( ObjectHelper::ToJSON( array( 'response' => false )));
                    }

                    GroupUserFactory::DeleteByMask( array(
                        'vkId'          =>  $user_id,
                        'sourceType'    =>  Group::STAT_GROUP
                    ));
                    GroupUserFactory::AddRange($groupUserArray);

                    die( ObjectHelper::ToJSON( array( 'response' => true )));
                }
            }
            $m_class  = $type . 'Groups';
            if ( !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }


            die( ObjectHelper::ToJSON( array( 'response' => 'true' )));
        }

        private function replace( $group_id, $new_place, $groupUserArray ) {
            $i = 0;
            //если сортировки не было - добавляем, попутно находим старое положение категории
            foreach($groupUserArray as $gu) {
                ++$i;
                if(!$gu->place)
                    $gu->place = $i;

                if( $gu->groupId == $group_id) {
                    $old_place = $gu->place;
                    $moved = $gu;
                }
            }
            if( $new_place > count($groupUserArray ) || !isset( $moved )) {
                return array();
            }

            $groupUserArray = ArrayHelper::Collapse( $groupUserArray, 'place', false );
            if ( $old_place - $new_place > 0 ) {

                for ( $i = $new_place; $i <= $old_place; $i++ ) {
                    $moved_tmp = $groupUserArray[$i];
                    $groupUserArray[$i] = $moved;
                    $groupUserArray[$i]->place = $i;
                    $moved = $moved_tmp;
                }
            } else {
                for ( $i = $old_place; $i < $new_place; $i++ ) {
                    $groupUserArray[$i] = $groupUserArray[$i + 1];
                    $groupUserArray[$i]->place = $i;
                }
                $groupUserArray[$new_place] = $moved;
                $groupUserArray[$i]->place = $new_place;
            }
            return $groupUserArray;
        }


    }