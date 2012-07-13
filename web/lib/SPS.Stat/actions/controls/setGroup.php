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

            $query = 'SELECT * FROM groups WHERE "name"=@name AND user_id=@userId';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@userId', $userId);
            $cmd->SetString('@name',    $groupName);

            $ds = $cmd->Execute();
            print_r($ds);
            $ds->next();
            echo '<br>';
            print_r($ds);
            if($ds->getValue('name')) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            //rename
            if ($groupId) {
                $query = 'UPDATE groups SET "name"=@name WHERE group_id=@group_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id',   $groupId);
                $cmd->SetString('@name',        $groupName);
                $cmd->Execute();

                echo $query;
            //new
            } elseif($groupName) {
                $query = 'INSERT INTO groups("name",user_id) VALUES(@name, @user_id) RETURNING group_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@user_id', $userId);
                $cmd->SetString('@name',     $groupName);
                $ds = $cmd->Execute();
                $ds->next();
                print_r($ds);
                $id = $ds->getValue('group_id', TYPE_INTEGER);

                $query = 'INSERT INTO publ_rels_names(user_id,publ_id,group_id) VALUES(@user_id,@publ_id,@group_id)';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@user_id', $userId);
                $cmd->SetInteger('@publ_id', $publId);
                $cmd->SetInteger('@group_id',$id);
                $cmd->Execute();

//
            }
            echo ObjectHelper::ToJSON(array('response' => true));

        }
    }

?>
