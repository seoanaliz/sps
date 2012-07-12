<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class deleteGroup extends wrapper {

        /**
         * Entry Point
         */
        public function Execute() {
            //$userId   = Request::getInteger( 'userId' );
            //$userId = AuthVkontakte::IsAuth();
            $groupId  = Request::getInteger ( 'groupId' );
            if (!$groupId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                return;
            }

            $query = 'DELETE FROM publ_rels_names WHERE group_id = @groupId';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@groupId', $groupId);
            $cmd->Execute();

            $query = 'DELETE FROM groups WHERE group_id = @groupId';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@groupId', $groupId);
            $cmd->Execute();

            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>