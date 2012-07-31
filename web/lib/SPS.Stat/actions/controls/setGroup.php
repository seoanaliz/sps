<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

//создает новую группу
    class setGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            $userId     =   Request::getInteger( 'userId' );
            $groupId    =   Request::getInteger( 'groupId' );
            $groupName  =   Request::getString ( 'groupName' );
            $ava        =   Request::getString ( 'ava' );
            $comments   =   Request::getString ( 'comments' );
            $general    =   Request::getInteger ( 'general' );

            $general = $general ? $general : 0;
            $groupId = $groupId ? $groupId : 0;

            $ava        = $ava      ? $ava     : NULL;
            $comments   = $comments ? comments : NULL;

            //todo одинаковые группы убить
            if (!$groupName || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if ($general && !StatUsers::is_Sadmin($userId)) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
                    //если мы создаем general группу, ее надо применить ко всем юзерам, посему
                    //вместо id текущего юзера мы посылаем массив всех
              elseif ($general && !$groupId)
                  $userId = StatUsers::get_users();

            $newGroupId = StatGroups::setGroup($ava, $groupName, $comments, $groupId);
            if (!$newGroupId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            if (!$groupId)
                StatGroups::implement_group($newGroupId, $userId);

            echo ObjectHelper::ToJSON(array('response' => $newGroupId));

        }

    }

?>
