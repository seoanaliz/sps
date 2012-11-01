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
        $this->now = new DateTimeWrapper(date( 'Y-m-d H:i:s',time()+ 10800));
        //получить список активных эвентов (статус = 3 )
        //прогнать нужные стены(10 записей), поиск search_string
        //не найдено - статус 5, собрать визиторов и население
//        error_reporting(0);

        $barters_for_search = BarterEventFactory::Get( array( 'status' => 3 ), null, 'tst' );
        $this->search_for_posts( $barters_for_search );
        $this->update_population( $barters_for_search );
        BarterEventFactory::UpdateRange( $barters_for_search, null, 'tst' );
    }

    public function search_for_posts( $barter_events_array )
    {
        $chunks = array_chunk( $barter_events_array, 25 );
        foreach( $chunks as $chunk ) {

            $res = StatPublics::get_publics_walls( $chunk );
            $barter = reset( $chunk );
            $overposts = '';
            foreach( $res as $wall ) {
                unset($wall[0]);
                $trig = false;
                foreach( $wall as $post ) {

                    if( $post->id  == $barter->post_id ) {
                        if( $barter->stop_search_at->compareTo( $this->now ) < 0 ) {
                            $barter->status = 4;
                            $barter->deletedAt = 0;
                        }
                        $trig = true;
                        break;
                    } elseif( $post->id < $barter->post_id  ) {
                        $barter->status = 5;
                        $barter->deleted_at = $this->now;
                        break;
                    } else {
                        $overposts .= $post->id . ',';
                    }
                }

                $barter->barter_overlaps = rtrim( $overposts, ',');

                if ( !$trig && !$this->check_post_existence( $barter ) ) {
                    $barter->status = 5;
                    $barter->deleted_at = $this->now;
                }

                $barter = next( $chunk );
            }
        }
    }

    public function check_post_existence( $barter_event )
    {
        $res = VkHelper::api_request( 'wall.getById', array(
            'posts' => '-' . $barter_event->barter_public . '_' . $barter_event->post_id ), 0 );
        //todo ошибки
        if ( empty( $res )) {
            $barter_event->status = 5;
            return false;
        }
        return true;
    }

    public function update_population( $barter_events_array )
    {
        foreach( $barter_events_array as $barter_event ) {
            if ( $barter_event->status != 3 ) {
                $time = time() + 10800;
                $res =  StatPublics::get_visitors_from_vk( $barter_event->target_public, $time, $time );
                print_r($res);
                $barter_event->end_visitors = $res['visitors'];
                $res = VkHelper::api_request( 'groups.getMembers', array( 'gid' => $barter_event->target_public, 'count' => 1 ), 0 );
                $barter_event->end_subscribers = $res->count;
                sleep(0.3);
            }
        }
    }
}
