<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

    //subtract public from group
    class exEntryFromGroup {

        /**
         * Entry Point
         */

        public function Execute() {
            error_reporting( 0 );
            $user_id   = AuthVkontakte::IsAuth();
            $group_id  = Request::getInteger ( 'groupId' );
            $entry_id  = Request::getInteger ( 'publId'  );
            if ( !$entry_id )
                $entry_id = Request::getInteger ( 'entryId'  );
            $general   = Request::getInteger ( 'general' );
            $type      = ucfirst( Request::getString( 'type' ));

            $type_array = array( 'Stat', 'Mes', 'Barter' );
            if ( !$type || !in_array( $type, $type_array ))
                $type  = 'Stat';
            $m_class = $type . 'Groups';
            $general = $general ? $general : 0;

            if ( !$group_id || !$user_id || !$entry_id ) {

                die( ERR_MISSING_PARAMS );
            }

            if( $type == 'Barter' ) {
                $source = 1;
                //внимание, тут сбой в логике. Раньше ( до бартера ) принадлежность записи к группе определялась в спец таблице
                //поэтому записи приписывались к группе. Теперь эта инфа храниться в самой записи
                $default_group = GroupsUtility::get_default_group( $user_id, $source );
                $events = BarterEventFactory::Get( array( 'barter_event_id' => $entry_id ));
                GroupsUtility::extricate_from_group( $events, $group_id, $default_group->group_id );
                BarterEventFactory::UpdateRange( $events );
                die( ObjectHelper::ToJSON(array( 'response' => true )));
            } elseif( $type == 'Stat' ) {
                if( StatAccessUtility::CanEditGlobalGroups($user_id, Group::STAT_GROUP)) {
                    GroupEntryFactory::DeleteByMask( array(
                        'groupId'       =>  $group_id,
                        'entryId'       =>  $entry_id,
                        'sourceType'    =>  Group::STAT_GROUP,
                    ));
                    $response['success'] = true;
                    die( ObjectHelper::ToJSON(array( 'response' => true )));
                } else {
                    $response['message'] = "access denied";
                }
            }
            elseif (  StatUsers::is_Sadmin( $user_id )) {
                $m_class::extricate_entry( $group_id, $entry_id, $user_id );
                die( ObjectHelper::ToJSON( array('response' => true )));

            } else {
                die( ObjectHelper::ToJSON(array( 'response' => false )));
            }
        }
    }
?>