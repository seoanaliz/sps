<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class toggleGroupFave {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );

            $group_id  =  Request::getInteger( 'groupId' );
            $user_id   =  Request::getInteger( 'userId' );

            if ( !group_id || !$user_id ) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $query = 'UPDATE ' . TABLE_STAT_GROUP_USER_REL . ' SET fave = NOT fave WHERE user_id=@user_id AND group_id=@group_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger( '@user_id',  $user_id  );
            $cmd->SetInteger( '@group_id', $group_id );
            $cmd->Execute();
            echo  ObjectHelper::ToJSON( array( 'response' => true ) );

        }
    }
?>