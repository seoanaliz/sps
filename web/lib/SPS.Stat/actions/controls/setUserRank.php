<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setUserRank
    {
        /**
         * Entry Point
         */

        public function Execute() {

             error_reporting( 0 );
            $userId         =   Request::getInteger ( 'userId' );
            $rank           =   Request::getInteger ( 'rank'   );
            $recipientId    =   Request::getInteger ( 'recId'  );


            if ( !$recipientId || !$userId || !isset($rank) ) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if ( !StatUsers::is_Sadmin( $userId ) ) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $sql = 'UPDATE ' . TABLE_STAT_USERS . '
                       SET
                            rank = @rank
                       WHERE
                            user_id = @recId';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@recId', $recipientId);
            $cmd->SetInteger('@rank', $rank);
            $cmd->Execute();

            echo  ObjectHelper::ToJSON(array('response' => true));

        }
    }


?>