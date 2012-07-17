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
            error_reporting( 0 );

            $publId = Request::getInteger( 'publId' );
            $price  = Request::getInteger( 'price' );

            if (empty($publId)) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $price = $price ? $price : 0;

            $query = 'UPDATE publs50k SET price=@price WHERE vk_id=@publ_id';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@publ_id',    $publId);
            $cmd->SetInteger('@price',   $price);
            $cmd->Execute();

            echo ObjectHelper::ToJSON(array('response' => true));
        }
    }

?>
