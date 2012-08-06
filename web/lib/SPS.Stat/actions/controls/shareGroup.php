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
            $userId         =   Request::getInteger( 'userId' );
            $groupId        =   Request::getInteger( 'groupId' );
            $recipientId    =   Request::getInteger( 'recId' );
            $general        =   Request::getInteger( 'general' );

            $general        =   $general ? $general : 0;

            if ( !$groupId || !$userId || !$recipientId ) {
                die( ERR_MISSING_PARAMS );
            }

            if ( $general && !StatUsers::is_Sadmin( $userId ) ) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }


            if ( StatGroups::implement_group($groupId, $recipientId) )
                die( ObjectHelper::ToJSON(  array( 'response' => true ) ) );
            else
                die( ObjectHelper::ToJSON( array( 'response' => false ) ) );

        }


    }

?>
