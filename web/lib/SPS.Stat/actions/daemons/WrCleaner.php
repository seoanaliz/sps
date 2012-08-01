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
        private $ids;

        public function Execute() {

//             $targetFeed = TargetFeedFactory::GetById(, array(), array(BaseFactory::WithLists => true));
            $targetFeed = TargetFeedFactory::Get();
            foreach( $targetFeed as $public ) {

                if ($public->type != 'vk' )
                    continue;
                if ($public->externalId == 35807078 || $public->externalId == 26776509)
                    continue;

                echo '<br>';

                $params = array (
                    'owner_id'  =>  '-' . $public->externalId,
                    'count'     =>  10,
                    'offset'    =>  -1,
                    'sort'      =>  'desc',
                );

                $wallposts = wrapper::vk_api_wrap('wall.get', $params);
                unset($wallposts[0]);
                foreach ( $wallposts as $post ) {
                    $this->CheckComments($public->externalId, $post->id);
                }
                sleep(0.3);
            }



        }

        public function get_offset($public_id) {
            $params = array(
                'owner_id'  =>  '-' . $public_id,
                'count'     =>  1
            );
            $a = wrapper::vk_api_wrap('wall.get', $params);
            sleep(0.3);
            return $a[0] > 10 ? $a[0] - 10 : 0 ;

        }

        public function CheckComments($public_id, $post_id)
        {
            $params = array(
                'owner_id'  =>  '-' . $public_id,
                'post_id'   =>  $post_id,
                'count'     =>  10,
                'sort'      =>  'desc'
            );

            $res = wrapper::vk_api_wrap('wall.getComments', $params);
            sleep(0.3);
            unset($res[0]);

            foreach ($res as $comment) {
                if ( preg_match('/http\:\/\/[^\s]+/',$comment->text) ) {
                    $params = array(
                        'owner_id'  =>  '-' . $public_id,
                        'cid'   =>  $comment->cid,

                    );

                    $res = wrapper::vk_api_wrap('wall.deleteComment', $params);
                    echo '<br>we are deleting in here!<br>';
                    echo '<a href="http://vk.com/wall-' . $public_id . '_' . $post_id . '"><br>' . $comment->text;
                    //print_r($res);
                    echo '<br><br>';

                }
            }
        }
    }