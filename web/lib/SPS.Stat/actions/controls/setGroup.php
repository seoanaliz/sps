<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setGroup extends wrapper {

        /**
         * Entry Point
         */
        public function Execute() {

            $publId = Request::getInteger( 'publId' );
            $group = Request::getInteger( 'group' );

            if (empty($publId)) {
                return;
            }

            //todo регистрация
            $query = sprintf('UPDATE publs50k SET group=%1$s WHERE vk_id=%2$d', $group, $publId);
            $this->db_wrap('query', $query);

        }
    }

?>
