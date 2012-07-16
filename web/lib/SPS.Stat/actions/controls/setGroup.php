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
            error_reporting(0);
            $userId     =   Request::getInteger( 'userId' );
            $groupId    =   Request::getInteger( 'groupId' );
            $groupName  =   Request::getString ( 'groupName' );
            $groupName  =   $groupName ? $groupName : 0;
            if (!$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            $query = 'SELECT * FROM groups WHERE "name"=@name AND user_id=@userId';
            $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@userId', $userId);
            $cmd->SetString('@name',    $groupName);

            $ds = $cmd->Execute();
            $ds->next();

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
            //new
            } elseif($groupName) {
                $query = 'INSERT INTO groups("name",user_id) VALUES(@name, @user_id) RETURNING group_id';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@user_id', $userId);
                $cmd->SetString('@name',     $groupName);
                $ds = $cmd->Execute();
                $ds->next();
                $id = $ds->getValue('group_id', TYPE_INTEGER);

                $query = 'INSERT INTO publ_rels_names(user_id,group_id) VALUES(@user_id,@group_id)';
                $cmd = new SqlCommand( $query, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@user_id', $userId);
                $cmd->SetInteger('@group_id',$id);
                $cmd->Execute();

//
            }
            echo ObjectHelper::ToJSON(array('response' => true));

        }
    }

?>
