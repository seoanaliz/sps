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
            $userId     = Request::getInteger( 'userId' );
            $groupName  = Request::getString ( 'groupName' );
            if (!$userId || !$groupName) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                return;
            }

            $query = sprintf('DELETE FROM publ_rels_names WHERE user_id=%1$d AND group_name=\'%2$s\''
                , $userId, $groupName);
            $this->db_wrap('query', $query);
            echo  ObjectHelper::ToJSON(array('response' => true));
        }
    }
?>