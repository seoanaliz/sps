<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class exGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            $userId   = Request::getInteger ( 'userId' );
            $groupId  = Request::getInteger ( 'groupId' );
            $publId   = Request::getInteger ( 'publId' );
            if (!$groupId || !$userId || !$publId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $query =    'DELETE FROM publ_rels_names
                         WHERE group_id=@group_id AND user_id=@user_id AND publ_id=@publ_id';

            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->SetInteger('@publ_id', $publId);
            $cmd->SetInteger('@user_id', $userId);
            $cmd->Execute();

            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>