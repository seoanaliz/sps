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
            $user_id    =   Request::getInteger( 'userId' );
            $type       =   Request::getString ( 'type' );
           // $vk_lists   =   Request::getInteger( 'vkLists' );

            $type_array = array( 'Stat', 'Mes', 'stat', 'mes');
            if ( !$type || !in_array( $type, $type_array, 1 ) )
                $type = 'Stat';

            $m_class  = $type . 'Groups';

            if ( !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }

            $res = $m_class::get_groups( $user_id );

            echo ObjectHelper::ToJSON(array('response' => $res));
        }


    }