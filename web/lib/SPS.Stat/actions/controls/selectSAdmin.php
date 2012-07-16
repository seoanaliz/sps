<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class selectSAdmin extends wrapper {


        public function Execute() {
            error_reporting( 0 );
            $adminId    = Request::getInteger( 'adminId' );
            $publId     = Request::getInteger( 'publId' );
            $groupId    = Request::getInteger(  'groupId' );
            $userId     = Request::getInteger( 'userId' );
            if (!$adminId || !$publId || !$groupId || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $sql = 'UPDATE
                        publ_rels_names
                    SET
                        selected_admin=@admin_id
                    WHERE
                        publ_id=@publ_id AND group_id=@group_id AND user_id=@user_id';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id',    $userId);
            $cmd->SetInteger('@group_id',   $groupId);
            $cmd->SetInteger('@publ_id',    $publId);
            $cmd->SetInteger('@admin_id',   $adminId);
            $cmd->Execute();

            echo ObjectHelper::ToJSON(array('response' => true));
        }
    }

?>