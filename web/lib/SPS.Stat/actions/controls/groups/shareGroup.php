<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

//создает новую группу
    class shareGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            $user_id        =   Request::getInteger( 'userId' );
            $group_ids      =   Request::getString(  'groupId' );
            $recipients_id  =   Request::getString(  'recId' );
            $general        =   Request::getInteger( 'general' );
            $type           =   Request::getString ( 'type' );

            $type_array = array( 'Stat', 'Mes', 'stat', 'mes');
            if ( !$type || !in_array( $type, $type_array, 1 ) )
                $type = 'Stat';
            $m_class    = $type . 'Groups';
            $general    = $general ? $general : 0;
            if ( !$group_ids || !$user_id || !$recipients_id ) {
                die( ERR_MISSING_PARAMS );
            }

            $recipients_id  = explode( ',', $recipients_id );
            $group_ids      = explode( ',', $group_ids );

            if ( $general && !StatUsers::is_Sadmin( $user_id ) ) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
	     
            $m_class::implement_group( $group_ids, $recipients_id );
            die( ObjectHelper::ToJSON( array( 'response' => true )));

        }


    }

?>
