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
            $sql = 'SELECT * FROM groups WHERE user_id=' . $userId;
            $this->db_wrap('query', $sql);
            $res = array();
            while ($row = $this->db_wrap('get_row'))
                array_push($res, $row);

            ksort($res);
            echo ObjectHelper::ToJSON(array('response' => $res));
        }

    }