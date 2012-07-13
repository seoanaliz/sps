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
            $publId     = Request::getInteger( 'publId' );
            $userId     = Request::getInteger( 'userId' );
            $prevGroupName  = Request::getString ( 'prevGroupName' );
            $groupName  = Request::getString ( 'groupName' );
            $groupName  = $groupName ? $groupName : 0;
            if (!$publId || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $query = sprintf('SELECT * FROM publ_rels_names WHERE group_name=\'%1$s\' AND publ_id=%2$d AND user_id=%3$d'
                ,$groupName, $publId, $userId);
            echo $query . '<br>';
            $this->db_wrap('query', $query);
            if ($this->db_wrap('affected_rows'))
            {
                echo ObjectHelper::ToJSON(array('response' => false, 'mess' => 'Entry already was in table'));
                die();
            }
            if ($prevGroupName) {
                $query = sprintf('UPDATE publ_rels_names SET group_name=\'%1$s\' WHERE publ_id=%2$d AND user_id=%3$d AND group_name=\'%4$s\''
                    ,$groupName, $publId, $userId, $prevGroupName);
            }
            else
                $query = sprintf('INSERT INTO publ_rels_names(user_id, publ_id, group_name) VALUES (%3$d,%2$d,\'%1$s\')'
                    ,$groupName, $publId, $userId);

            $this->db_wrap('query', $query);
            echo ObjectHelper::ToJSON(array('response' => true));
        }
    }

?>
