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

            $user_id    =   AuthVkontakte::IsAuth();
            if( !$user_id ) {
                echo  ObjectHelper::ToJSON( array('response' => false ));
            }
            $author = AuthorFactory::GetOne( array('vkId' => $user_id ));

            $user = array(
                'userId'    =>  $user_id,
                'rank'      =>  StatAccessUtility::GetRankInSource($user_id, Group::STAT_GROUP),
                'ava'       =>  $author->avatar,
                'name'      =>  $author->FullName(),
                'comments'  =>  ''
            );
            if ( $user ) {
                echo  ObjectHelper::ToJSON( array('response' => $user ));
            }
            else {
                echo  ObjectHelper::ToJSON( array('response' => false ));
            }

        }
    }
?>