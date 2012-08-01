<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

    //добавляет юзера в бд (если его там нет)
    //и возвращает данные о нем
    class addUser {

        /**
         * Entry Point
         */

        public function Execute() {
            error_reporting( 0 );

            $userId     =   Request::getInteger( 'userId' );
            $rank       =   Request::getInteger( 'rank' );
            $comments   =   Request::getString ( 'uComments' );

            $rank = $rank ? $rank : 0;

            $comments = $comments ? $comments : NULL;
            if (!$userId) {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }


            $user = StatUsers::is_our_user($userId);
            if ($user) {
                echo  ObjectHelper::ToJSON(array('response' => $user));
                die();
            }

            $user = StatUsers::get_vk_user_info($userId);
            $user['rank']     = $rank;
            $user['comments'] = $comments;
            $user = StatUsers::add_user($user);
            if ($user)
                echo  ObjectHelper::ToJSON(array('response' => $user));
            else
                echo  ObjectHelper::ToJSON(array('response' => false));

        }
    }
?>