<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class selectGroupAdmin {


        public function Execute() {
            error_reporting( 0 );
            $adminId    = Request::getInteger( 'adminId' );
            $groupId    = Request::getInteger( 'groupId' );
            $userId     = Request::getInteger( 'userId' );
            $general    = Request::getInteger ( 'general' );

            $general = $general ? $general : 0;

            if (!$adminId || !$groupId || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if ( !$general || ($general && StatUsers::is_Sadmin($userId) ) ) {
                StatGroups::select_main_admin($groupId, $adminId);
                echo ObjectHelper::ToJSON(array('response' => true));
                die();
            }
            echo ObjectHelper::ToJSON(array('response' => false));

        }
    }

?>