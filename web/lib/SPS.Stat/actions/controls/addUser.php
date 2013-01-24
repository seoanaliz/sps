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

            $user_id    =   Request::getInteger( 'userId' );
            $rank       =   Request::getInteger( 'rank' );
            $comments   =   Request::getString ( 'uComments' );

            $rank = $rank ? $rank : 0;

            $comments = $comments ? $comments : NULL;
            if ( !$user_id )
                die(ERR_MISSING_PARAMS);

            $user = StatUsers::is_our_user( $user_id );
            if ( $user ) {
                $user['at'] = StatUsers::get_access_token( $user_id ) ? 1 : 0;
                if ( $user['at']) {
//                    MesDialogs::check_friend_requests( $user_id );
                   if ( !MesDialogs::check_new_messages( array( $user_id ), 30 ))
                       die( ERR_NO_ACC_TOK );
                }
                die(  ObjectHelper::ToJSON( array( 'response' => $user )));
            }

            $users = StatUsers::get_vk_user_info( $user_id );
            foreach ( $users as $user ) {
                $user['rank']     = $rank;
                $user['comments'] = $comments;
                $user = StatUsers::add_user( $user );
                $user['at']       = StatUsers::get_access_token( $user_id ) ? 1 : 0;
            }

            if ( $user ){
                echo  ObjectHelper::ToJSON(array('response' => $user));
            }
            else
                echo  ObjectHelper::ToJSON( array( 'response' => false ) );

        }
    }
?>