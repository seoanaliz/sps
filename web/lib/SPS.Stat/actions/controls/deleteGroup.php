<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
//удалить группу, обычную - для юзера, general - для всех юзеров
    class deleteGroup {

        /**
         * Entry Point
         */

        public function Execute() {

            error_reporting( 0 );
            $userId   = Request::getInteger( 'userId' );
            $groupId  = Request::getInteger ( 'groupId' );
            $general  = Request::getInteger ( 'general' );

            $general = $general ? $general : 0;

            if (!$groupId || !$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if ($general AND statUsers::is_Sadmin($userId)) {
                $query = 'DELETE FROM
                            ' . TABLE_STAT_GROUP_USER_REL . '
                         WHERE
                                group_id=@group_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            } elseif (!$general) {
                $query = 'DELETE FROM '
                                    . TABLE_STAT_GROUP_USER_REL . '
                                WHERE
                                      group_id=@group_id AND user_id=@user_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@user_id', $userId);
            } else {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $cmd->SetInteger('@group_id', $groupId);
            $cmd->Execute();

            //вообще спорно, зачем их удалять
//            if ($affectAllUsers) {
//                $query = 'DELETE FROM groups WHERE group_id=@group_id';
//                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
//                $cmd->SetInteger('@group_id', $groupId);
//                $cmd->Execute();
//            }
            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>