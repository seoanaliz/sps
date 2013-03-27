<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 21:06
 * To change this template use File | Settings | File Templates.
 */
class CheckPosts
{
    private $now;

    public function Execute()
    {
        $this->now = new DateTimeWrapper(date( 'Y-m-d H:i:s',time()));
//        error_reporting(0);
        $barters_for_search = BarterEventFactory::Get( array( 'status' => 3 ), null, 'tst' );

        $this->search_for_posts( $barters_for_search );

        StatPublics::update_population( $barters_for_search );
        BarterEventFactory::UpdateRange( $barters_for_search, null, 'tst' );
    }

    public function search_for_posts( $barter_events_array )
    {
        $chunks = array_chunk( $barter_events_array, 25 );
        foreach( $chunks as $chunk ) {
            $res = StatPublics::get_publics_walls( $chunk, 'barter' );
            $barter = reset( $chunk );

            foreach( $res as $wall ) {
                $overposts = '';
                unset($wall[0]);
                $trig = false;

                foreach( $wall as $post ) {
                    if( $post->id  == $barter->post_id ) {
                        if( time() >= StatBarter::TIME_INTERVAL + $barter->detected_at->format('U')) {
                            $barter->status = 4;
                            $barter->deletedAt = $this->now;
                        }
                        $trig = true;
                        break;
                    } elseif( $post->id < $barter->post_id ) {
                        $barter->status = 6;
                        $barter->deleted_at = $this->now;
                        break;
                    } else {
                        if( $barter->stop_search_at->compareTo( new DateTimeWrapper(date('Y-m-d H:i:s', $post->date + StatPublics::time_shift )))> 0  )
                            $overposts .= $post->date . ',';
                    }
                }

                $barter->barter_overlaps = rtrim( $overposts, ',' );

                if ( !$trig && !$this->check_post_existence( $barter )) {
                    $barter->status = 6;
                    $barter->deleted_at = $this->now;
                }

                $barter = next( $chunk );
            }
        }
    }

    public function check_post_existence( $barter_event )
    {
        $res = VkHelper::api_request( 'wall.getById', array(
            'posts' => '-' . $barter_event->barter_public . '_' . $barter_event->post_id ), 0, 'barter' );
        //todo ошибки
        if ( empty( $res )) {
            $barter_event->status = 5;
            return false;
        }
        return true;
    }

    public function get_public_members_count( $public_id )
    {
        $count = 0;
        for ( $i = 0; $i < 3; $i++ ) {
            $params = array(
                'gid'       =>  $public_id,
                'fields'    =>  'members_count'
            );

            $res = VkHelper::api_request( 'groups.getById', $params, 0, 'barter' );
            if ( isset( $count->error)) {
                sleep(0.6);
                continue;
            }
            $count = $res[0]->members_count;
            break;
        }
        return $count;
    }
}