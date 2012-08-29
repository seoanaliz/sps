<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setPostPrice {

        /**
         * Entry Point
         */
        public function Execute() {
//            error_reporting( 0 );
            $a = MesDialogs::get_dialog_id( 670456, 12 );
            print_r ($a);
            die();
            $publicId = Request::getInteger( 'publId' );
            $price  = Request::getInteger( 'price' );

            if (empty($publId)) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $price = $price ? $price : 0;

            $query = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET price=@price WHERE vk_id=@publ_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@publ_id',    $publicId);
            $cmd->SetInteger('@price',   $price);
            $cmd->Execute();

            echo ObjectHelper::ToJSON(array('response' => true));
        }
    }

?>
