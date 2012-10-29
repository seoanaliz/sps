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
//            error_reporting( 0 );
            $user_id    =   Request::getInteger( 'userId' );
            $group_ids  =   Request::getString ( 'groupIds' );
            $type       =   Request::getString ( 'type' );

            $type_array = array( 'Stat', 'Mes', 'stat', 'mes');
            if ( !$type || !in_array( $type, $type_array, 1 ) )
                $type = 'Stat';

            $m_class  = $type . 'Groups';
            if ( !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }

            $res = $m_class::set_lists_order( $user_id, $group_ids );

            echo ObjectHelper::ToJSON( array( 'response' => 'true' ));
        }
    }