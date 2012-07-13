<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
    * addPrice Action
    * @package    SPS
    * @subpackage Stat
    */
    class getGroupList extends wrapper
    {

    /**
    * Entry Point
    */
        public function Execute()
        {
            $userId = Request::getInteger( 'userId' );
            if (!$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                return;
            }
            $sql = 'SELECT DISTINCT(group_name) FROM publ_rels_names WHERE user_id=' . $userId;
            $this->db_wrap('query', $sql);
            $res = array();
            while ($row = $this->db_wrap('get_row'))
                $res[] = $row['group_name'];
            ksort($res);
            echo ObjectHelper::ToJSON($res);
        }

    }