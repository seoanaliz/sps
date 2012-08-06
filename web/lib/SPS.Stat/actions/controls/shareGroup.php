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
            $group_ids      =   Request::getString( 'groupId' );
            $recipients_id  =   Request::getString( 'recId' );
            $general        =   Request::getInteger( 'general' );

            $general        =   $general ? $general : 0;

            if ( !$group_ids || !$user_id || !$recipients_id ) {
                die( ERR_MISSING_PARAMS );
            }

            $recipients_id  = explode( ',', $recipients_id );
            $group_ids      = explode( ',', $group_ids );

            if ( $general && !StatUsers::is_Sadmin( $user_id ) ) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if ( StatGroups::implement_group($group_ids, $recipients_id) )
                die( ObjectHelper::ToJSON( array( 'response' => true ) ) );
            else
                die( ObjectHelper::ToJSON( array( 'response' => false ) ) );

        }


    }

?>
