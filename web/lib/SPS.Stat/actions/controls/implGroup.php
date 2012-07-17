<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class implGroup {

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


            $query = 'SELECT * FROM publ_rels_names
                      WHERE group_id=@group_id AND publ_id=@publ_id AND userId=@userId';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->SetInteger('@user_id', $userId);
            $cmd->SetInteger('@publ_id', $publId);
            $ds = $cmd->Execute();
            $ds->next();
            if($id = $ds->getValue('group_id')) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $query = 'SELECT * FROM groups
                      WHERE group_id=@group_id ';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $ds = $cmd->Execute();
            $ds->next();
            if(!$ds->getValue('group_id')) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $query = 'INSERT INTO publ_rels_names(user_id,publ_id,group_id)
                      VALUES (@user_id,@publ_id,@group_id)';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->SetInteger('@user_id', $userId);
            $cmd->SetInteger('@publ_id', $publId);
            $cmd->Execute();

            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>