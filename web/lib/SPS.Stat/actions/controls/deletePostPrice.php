<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class deletePostPrice extends wrapper {

        /**
         * Entry Point
         */
        public function Execute() {
            $publId = Request::getInteger( 'publId' );

            if (empty($publId) || empty($price)) {
                return;
            }

            //todo регистрация
            $query = sprintf('UPDATE publs50k SET price=%1$d WHERE vk_id=%2$d', '0', $publId);
            $this->db_wrap('query', $query);
            
        }
    }

?>
