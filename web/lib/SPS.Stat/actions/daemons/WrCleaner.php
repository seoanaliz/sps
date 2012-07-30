<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 30.07.12
 * Time: 20:16
 * To change this template use File | Settings | File Templates.
 */
    Package::Load( 'SPS.Stat' );

    set_time_limit(600);
    class WrCleaner extends wrapper
    {
        const TESTING = false;
        const T_PUBLICS_POINTS = 'gr50k';
        const T_PUBLICS_LIST   = 'publs50k';

        private $ids;

        public function Execute() {

            $public_id  =   27421965;
            $post_id    =   2372;
            $params = array(
                            'owner_id'  =>  '-' . $public_id,
                            'post_id'   =>  $post_id,
                            'count'     =>  100
            );

//            $inst = new wrapper;
            $res = wrapper::vk_api_wrap('wall.getComments', $params);
            print_r($res);
            unset($res[0]);

            foreach ($res as $comment) {
                if(substr_count($comment->text,'asf') > 0) {
                    $params = array(
                        'owner_id'  =>  '-' . $public_id,
                        'cid'   =>  $comment->cid,

                    );

                    $res = wrapper::vk_api_wrap('wall.deleteComment', $params);
                    echo '<br><br>';
                    print_r($res);
                    echo '<br><br>';

                }
            }

        }
    }