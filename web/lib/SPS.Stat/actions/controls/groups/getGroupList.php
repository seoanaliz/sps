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
                        $type     = ucfirst( Request::getString( 'type' ));

            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array ))
                $type    = 'Stat';

            $m_class  = $type . 'Groups';
            if ( !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }
            if ( $type == 'Barter' ) {
                $source = 1;
                $groups = GroupFactory::Get( array( 'status' => 1, 'source' => $source ));
                die( ObjectHelper::ToJSON( array( 'response' => GroupsUtility::form_response( $groups, $user_id ))));
            }
            $res = $m_class::get_groups( $user_id );

            die( ObjectHelper::ToJSON( array( 'response' => $res )));
        }
    }