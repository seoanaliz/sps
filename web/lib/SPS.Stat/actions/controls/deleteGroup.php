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
            $user_id  = Request::getInteger ( 'userId'  );
            $group_id  = Request::getInteger ( 'groupId' );
            $general  = Request::getInteger ( 'general' );
            $type     =   Request::getString ( 'type' );

            $type_array = array( 'Stat', 'Mes');
            if ( !$type || !in_array( $type, $type_array, 1 ) )
                $type = 'Stat';
            $m_class    = $type . 'Groups';

            $general = $general ? $general : 0;

            if ( !$group_id || !$user_id ) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();

            }

            if ( $general AND statUsers::is_Sadmin( $user_id ) ) {
                $res = $m_class::delete_group( $group_id );

            } elseif (!$general) {
                $res = $m_class::unsign_user_from_group( $group_id, $user_id );

            } else {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if ( $res ) {
                echo  ObjectHelper::ToJSON(array('response' => true));
                die();
            }

            echo  ObjectHelper::ToJSON(array('response' => false));
        }
    }
?>