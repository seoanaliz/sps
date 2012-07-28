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
            $userId = Request::getInteger( 'userId' );

            if (!$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $res = StatGroups::get_groups($userId);

            echo ObjectHelper::ToJSON(array('response' => $res));
        }

    }