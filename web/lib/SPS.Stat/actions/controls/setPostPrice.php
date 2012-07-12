<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setPostPrice extends wrapper {

        /**
         * Entry Point
         */
        public function Execute() {
            $publId = Request::getInteger( 'publId' );
            $price  = Request::getInteger( 'price' );

            if (empty($publId)) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $price = $price ? $price : 0;

            $query = sprintf('UPDATE publs50k SET price=%1$d WHERE vk_id=%2$d', $price, $publId);
            $this->db_wrap('query', $query);

            echo ObjectHelper::ToJSON(array('response' => true));
        }
    }

?>
