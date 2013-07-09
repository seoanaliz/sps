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
            $user_id        =   AuthVkontakte::IsAuth();
            $group_ids      =   Request::getString(  'groupId' );
            $recipients_ids =   Request::getString(  'recId' );
            $general        =   Request::getInteger( 'general' );
            $type           =   ucfirst( Request::getString( 'type' ));
            $type_array = array( 'Stat', 'Mes', 'Barter' );

            if ( !$type || !in_array( $type, $type_array ))
                $type    = 'Stat';

            $m_class    = $type . 'Groups';
            $general    = $general ? $general : 0;
            if ( !$group_ids || !$user_id || !$recipients_ids ) {
                die( ERR_MISSING_PARAMS );
            }
            $recipients_ids  = explode( ',', $recipients_ids );
            $group_ids       = explode( ',', $group_ids );

            if ( $type == 'Barter') {
                $source = 1;
//                $default_group = GroupsUtility::get_default_group( $user_id, $source );
//                if ( in_array( $default_group->group_id, $group_ids ))
//                    die( ObjectHelper::ToJSON( array( 'response' => false, 'err_mess' => 'can\'t share default' )));
                $groups = GroupFactory::Get( array( '_group_id' => $group_ids ));
                GroupsUtility::share_groups( $groups, $recipients_ids );
                GroupFactory::UpdateRange( $groups );
                die( ObjectHelper::ToJSON( array( 'response' => true )));
            } elseif ( $type == 'Stat') {
                $group_user_array = array();
                foreach( $recipients_ids as $recipients_id) {
                    foreach( $group_ids as $group_id) {
                        new GroupUser( $group_id, $recipients_id, Group::STAT_GROUP );
                    }
                }
                GroupUserFactory::AddRange( $group_user_array );
                die( ObjectHelper::ToJSON( array( 'response' => true )));
            }

	     
            $m_class::implement_group( $group_ids, $recipients_ids );
            die( ObjectHelper::ToJSON( array( 'response' => true )));
        }
    }

?>
