<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
    * addPrice Action
    * @package    SPS
    * @subpackage Stat
    */
    class getGroupList
    {

    /**
    * Entry Point
    */
        public function Execute()
        {
            error_reporting( 0 );
            $userId = Request::getInteger( 'userId' );
            if (!$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                return;
            }
            $sql = 'SELECT * FROM groups WHERE user_id=@user_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $userId);
            $ds = $cmd->Execute();

            $res = array();
            while ($ds->Next()) {
                $res[$ds->getValue('group_id',TYPE_INTEGER)] = $ds->getValue('name');
            }

            ksort($res);
            print_r($res);
            echo ObjectHelper::ToJSON($res);
        }

    }