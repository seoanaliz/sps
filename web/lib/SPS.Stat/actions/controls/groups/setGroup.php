<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

//создает новую группу
    class setGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );

            $userId     =   Request::getInteger( 'userId' );
            $groupId    =   Request::getInteger( 'groupId' );
            $groupName  =   Request::getString ( 'groupName' );
            $ava        =   Request::getString ( 'ava' );
            $comments   =   Request::getString ( 'comments' );
            $general    =   Request::getInteger( 'general' );
            $type       =   Request::getString ( 'type' );

            $type_array = array( 'Stat', 'Mes', 'stat', 'mes');
            if ( !$type || !in_array( $type, $type_array, 1 ) )
                $type = 'Stat';
            $m_class    = $type . 'Groups';

            $general    = $general ? $general : 0;
            $groupId    = $groupId ? $groupId : 0;

            $ava        = $ava      ? $ava     : NULL;
            $comments   = $comments ? comments : NULL;


            if ( !$groupName || !$userId ) {
                die(ERR_MISSING_PARAMS);
            }

            if ( !$m_class::check_group_name_free( $userId, $groupName ) )  {
                echo ObjectHelper::ToJSON(array('response' => false, 'err_mess' =>  'already exist') );
                die();
            }

            if ( $general && !StatUsers::is_Sadmin( $userId ) ) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            //если мы создаем general группу, ее надо применить ко всем юзерам, посему
            //вместо id текущего юзера мы посылаем массив всех
             elseif ( $general && !$groupId )
                  $userId = StatUsers::get_users();

            $newGroupId = $m_class::setGroup( $ava, $groupName, $comments, $groupId );

            if ( !$newGroupId ) {
                echo ObjectHelper::ToJSON(array( 'response' => false ) );
                die();
            }

            if ( !$groupId )
                $m_class::implement_group( $newGroupId, $userId );

            echo ObjectHelper::ToJSON( array( 'response' => $newGroupId ) );

        }

    }

?>
