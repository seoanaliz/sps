<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            $userId     =   Request::getInteger( 'userId' );
            $groupId    =   Request::getInteger( 'groupId' );
            $groupName  =   Request::getString ( 'groupName' );
            if (!$groupName || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }


//            if ($id = $this->exist_check($groupName, $userId)) {
//                echo ObjectHelper::ToJSON(array('response' => array('id'    =>  (int)$id)));
//                die();
//            }

            //rename
            if ($groupId) {
                $query = 'UPDATE groups SET "name"=@name WHERE group_id=@group_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id',   $groupId);
                $cmd->SetString('@name',        $groupName);
                $cmd->Execute();
                echo ObjectHelper::ToJSON(array('response' => true));
                die();
            //new
            } elseif($groupName) {
                $query = 'INSERT INTO groups("name",user_id) VALUES(@name, @user_id) RETURNING group_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@user_id', $userId);
                $cmd->SetString('@name',     $groupName);
                $ds = $cmd->Execute();
                $ds->next();
                $id = $ds->getValue('group_id', TYPE_INTEGER);
                if (!$id || $id == NULL) {
                    echo ObjectHelper::ToJSON(array('response' => false));
                    die();
                }

                echo ObjectHelper::ToJSON( array('response' => array('id'    => $id)));
                die();
            }
            echo ObjectHelper::ToJSON(array('response' => false));
            die();
        }


    }

?>
