<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class deleteGroup extends wrapper {

        /**
         * Entry Point
         */
        public function Execute() {
            //$userId   = Request::getInteger( 'userId' );
            $groupId  = Request::getInteger ( 'groupId' );
            if (!$groupId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                return;
            }

            $query = sprintf('DELETE FROM publ_rels_names WHERE group_id=%1$d',
                 $groupId);
            $this->db_wrap('query', $query);

            $query = sprintf('DELETE FROM groups WHERE group_id=%1$d',
                $groupId);
            $this->db_wrap('query', $query);
            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>