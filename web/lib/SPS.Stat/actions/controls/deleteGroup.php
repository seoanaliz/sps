<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class deleteGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            //$userId   = Request::getInteger( 'userId' );
            $groupId  = Request::getInteger ( 'groupId' );
            if (!$groupId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $query = 'DELETE FROM publ_rels_names WHERE group_id=@group_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->Execute();


            $query = 'DELETE FROM groups WHERE group_id=@group_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->Execute();

            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>