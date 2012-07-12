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
            $publId     =   Request::getInteger( 'publId' );
            $userId     =   Request::getInteger( 'userId' );
            $groupId    =   Request::getInteger( 'groupId' );
            $groupName  =   Request::getString ( 'groupName' );
            $groupName  =   $groupName ? $groupName : 0;
            if (!$publId || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }


            $query = sprintf('SELECT * FROM groups WHERE "name"=\'%1$s\' AND user_id=%2$d'
                ,$groupName, $userId);
            $this->db_wrap('query', $query);
            if($this->db_wrap('affected_rows')) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            //rename
            if ($groupId) {
               $query = sprintf('UPDATE groups SET "name"=\'%1$s\' WHERE group_id=%2$d'
                        ,$groupName, $groupId);
               $this->db_wrap('query', $query);
            //new
            } elseif($groupName) {


                $query = sprintf('INSERT INTO groups("name",user_id) VALUES(\'%1$s\', %2$d) RETURNING group_id'
                    ,$groupName, $userId);

                $this->db_wrap('query', $query, 1);
                $a = $this->db_wrap('get_row');
                print_r($a);
                $query = sprintf('INSERT INTO publ_rels_names(user_id,publ_id,group_id) VALUES(%1$d, %2$d,%3$d)'
                    ,$userId,$publId, $a['group_id']);

                $this->db_wrap('query', $query);
            }
            echo ObjectHelper::ToJSON(array('response' => true));

        }
    }

?>
