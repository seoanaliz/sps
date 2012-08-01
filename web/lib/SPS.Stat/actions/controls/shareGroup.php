<?
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

//создает новую группу
    class shareGroup {

        /**
         * Entry Point
         */
        public function Execute() {
            error_reporting( 0 );
            $userId         =   Request::getInteger( 'userId' );
            $groupId        =   Request::getInteger( 'groupId' );
            $recipientId    =   Request::getInteger( 'recId' );

            if (!$groupId || !$userId || !$recipientId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }

            //todo проверка на наличие группы у юзера, хз, надо ли

            StatGroups::implement_group($groupId, $userId);

            echo ObjectHelper::ToJSON(array('response' => true));
            die();
        }


    }

?>
