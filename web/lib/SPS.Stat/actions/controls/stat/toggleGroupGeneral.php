<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    new stat_tables();
    class toggleGroupGeneral {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );

            $group_id  =  Request::getInteger( 'groupId' );
            $user_id   =  AuthVkontakte::IsAuth();
            if ( !StatUsers::is_Sadmin( $user_id ) ) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $query = 'UPDATE ' . TABLE_STAT_GROUPS . ' SET general = NOT general where group_id=@group_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger(  '@user_id',  $user_id  );
            $cmd->SetInteger(  '@group_id', $group_id );
            $cmd->SetInteger(  '@type',     StatGroups::GROUP_GLOBAL );
            $cmd->Execute();
//            echo $cmd->GetQuery();
            echo  ObjectHelper::ToJSON( array( 'response' => true ) );

        }
    }
?>