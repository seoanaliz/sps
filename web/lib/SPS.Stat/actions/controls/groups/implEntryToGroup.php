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
            $response  = array('success' => false);
            $user_id  = AuthVkontakte::IsAuth();
            $group_id  = Request::getInteger ( 'groupId' );
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

            if ( !$group_id || !$entry_id ) {
                $response['message'] = 'Wrong data';
                die(ObjectHelper::ToJSON($response));
            }

            if ( $type == 'Barter' ) {
                if ( !GroupsUtility::is_author( $group_id, $user_id ))
                    die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mes' => 'access denied' )));
                $barter_events = BarterEventFactory::Get( array( 'barter_event_id' => $entry_id ));
                GroupsUtility::implement_to_group( $barter_events, $group_id, 1 );
                BarterEventFactory::UpdateRange( $barter_events, null, 'tst' );
                die( ObjectHelper::ToJSON( array( 'response' => true )));

            } elseif( $type == 'Stat' ) {
                if( StatAccessUtility::CanEditGlobalGroups($user_id, Group::STAT_GROUP)) {
                    $check = GroupEntryFactory::GetOne( array( 'entryId' => $entry_id, 'sourceType' => Group::STAT_GROUP));
                    if( !empty( $check)) {
                        $response['message'] = 'В этом списке уже есть данная запись';
                        die(ObjectHelper::ToJSON($response));
                    }
                    $groupEntry = new GroupEntry( $group_id, $entry_id, Group::STAT_GROUP);

                    if( GroupEntryFactory::Add($groupEntry)) {
                        $response['success'] = true;
                        die( ObjectHelper::ToJSON(array( 'response' => true )));
                    }
                } else {
                    $response['message'] = "access denied";
                }

            }

            die( ObjectHelper::ToJSON( array( 'response' => false )));
        }
    }
?>