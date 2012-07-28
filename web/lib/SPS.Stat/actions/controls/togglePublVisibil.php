<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class togglePublVisibil {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );

            $publId  =  Request::getInteger ( 'publId' );
            $userId  =  Request::getInteger( 'userId' );

            if (!$publId || !$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            //todo права юзеров
            $query = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET sh_in_main = NOT sh_in_main where vk_id=@vk_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@vk_id', $publId);
            $cmd->Execute();
            echo  ObjectHelper::ToJSON(array('response' => true));


        }
    }
?>