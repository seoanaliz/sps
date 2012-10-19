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

            $price              =   Request::getInteger ( 'price' );
            $complicate         =   Request::getFloat ( 'complicate' );
            $reposts            =   Request::getFloat ( 'reposts' );
            $rel_mark           =   Request::getFloat ( 'rel_mark' );
            $overposts          =   Request::getFloat ( 'overposts' );
            $sql = 'UPDATE ' . TABLE_OADMINS_CONF . 'SET price=@price,
                                                         complicate=@complicate,
                                                         reposts=@reposts,
                                                         rel_mark=@rel_mark,
                                                         overposts=@overposts';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetFloat( '@price', $price );
            $cmd->SetFloat( '@complicate', $complicate );
            $cmd->SetFloat( '@reposts', $reposts );
            $cmd->SetFloat( '@rel_mark', $rel_mark );
            $cmd->SetFloat( '@overposts', $overposts );
            $cmd->Execute();
        }
    }


?>