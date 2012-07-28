<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

    //добовляет паблик в группу
    class implPublic {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            $userId   = Request::getInteger ( 'userId' );
            $groupId  = Request::getInteger ( 'groupId' );
            $publicId   = Request::getInteger ( 'publId' );
            $general  = Request::getInteger ( 'general' );

            $general = $general ? $general : 0;

            if (!$groupId || !$publicId || !$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if (!$general || ( $general && StatUsers::is_Sadmin($userId))) {
                StatGroups::implement_public($groupId, $publicId);
                echo  ObjectHelper::ToJSON(array('response' => true));
                die();
            }

            echo  ObjectHelper::ToJSON(array('response' => false));
        }
    }
?>