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

            error_reporting( 0 );
            $user_id  = AuthVkontakte::IsAuth();
            $group_id = Request::getInteger ( 'groupId' );
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
                $group = GroupFactory::GetOne( array( 'group_id' => $group_id));
                if ( !$group )
                    die(  ObjectHelper::ToJSON(array('response' => false )));

                if ( $group->group_id === $default_group->group_id ) {
                    //дефолтные группы удалять нельзя
                    die(  ObjectHelper::ToJSON(array('response' => false )));
                } elseif ( $group && $group->created_by == $user_id ) {
                    //жесткое удаление группы, только создавший
                    GroupsUtility::delete_group( $group, $default_group );

                }
                  else {
                      //отписываем человека от группы
                      $group = GroupFactory::GetOne( array( 'group_id' => $group_id ));
                      GroupsUtility::dismiss_from_group( $group, $user_id );
                }
                die( ObjectHelper::ToJSON(array('response' => true )));
            }

            if ( $general AND statUsers::is_Sadmin( $user_id ) ) {
                $res = $m_class::delete_group( $group_id );

            } elseif ( !$general ) {
                $res = $m_class::extricate_group( $group_id, $user_id );

            } else {
                die( ObjectHelper::ToJSON(array('response' => false)));

            }

            if ( $res ) {
                die(  ObjectHelper::ToJSON(array('response' => true)));
            }

            die( ObjectHelper::ToJSON(array('response' => false)));
        }
    }
?>