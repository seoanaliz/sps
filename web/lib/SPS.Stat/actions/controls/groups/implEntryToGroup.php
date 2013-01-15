<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

    //добовляет паблик в группу
    class implEntryToGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            $user_id  = AuthVkontakte::IsAuth();
            $groupId  = Request::getInteger ( 'groupId' );
            $entry_id = Request::getInteger ( 'publId'  );
            if ( !$entry_id )
                $entry_id = Request::getInteger ( 'entryId'  );
            $general  = Request::getInteger ( 'general' );
            $type     = ucfirst( Request::getString( 'type' ));

            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array ))
                $type    = 'Stat';


            $m_class    = $type . 'Groups';
            $general    = $general ? $general : 0;

            if ( !$groupId || !$entry_id || !$user_id ) {
                die(ERR_MISSING_PARAMS);
            }

            if ( $type == 'Barter' ) {
                $source = 1;
                if ( !GroupsUtility::is_author( $groupId, $user_id ))
                    die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes' => 'access denied' )));
                $barter_events = BarterEventFactory::Get( array( 'barter_event_id' => $entry_id ));
                GroupsUtility::implement_to_group( $barter_events, $groupId, 1 );
                BarterEventFactory::UpdateRange( $barter_events, null, 'tst' );
                die( ObjectHelper::ToJSON( array( 'response' => true )));

            }

            if ( !$general || ( $general && StatUsers::is_Sadmin( $user_id ))) {
                $m_class::implement_entry( $groupId, $entry_id, $user_id );
                die( ObjectHelper::ToJSON( array( 'response' => true )));
            }

            die( ObjectHelper::ToJSON( array( 'response' => false )));
        }
    }
?>