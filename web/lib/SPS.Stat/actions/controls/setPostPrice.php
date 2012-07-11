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
            $price = Request::getInteger( 'price' );

            //$publId =25873533;
            //$price = 32;

            if (empty($publId) || empty($price)) {
                return;
            }

            //todo регистрация
            $query = sprintf('UPDATE publs50k SET price=%1$d WHERE vk_id=%2$d', $price, $publId);
            $this->db_wrap('query', $query);
        }
    }

?>
