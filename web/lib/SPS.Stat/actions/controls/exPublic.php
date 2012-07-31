<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    //subtract public from group
    class exPublic {

        /**
         * Entry Point
         */

          public function Execute() {
            error_reporting( 0 );

            $userId   = Request::getInteger ( 'userId'  );
            $groupId  = Request::getInteger ( 'groupId' );
            $publicId = Request::getInteger ( 'publId'  );
            $general  = Request::getInteger ( 'general' );

            $general = $general ? $general : 0;

            if (!$groupId || !$userId || !$publicId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            //todo не уверен, нужна ли проверка на "главность"
            if (    !$general
                    ||
                    ($general
                        && StatUsers::is_Sadmin($userId)
                        && StatGroups::is_general($groupId) )

                    ) {
                $query =  'DELETE FROM '
                                . TABLE_STAT_GROUP_PUBLIC_REL . '
                           WHERE
                                group_id=@group_id AND public_id=@publ_id';

                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id', $groupId);
                $cmd->SetInteger('@public_id', $publicId);
                $cmd->Execute();

                echo  ObjectHelper::ToJSON(array('response' => true));

            }




        }
    }
?>