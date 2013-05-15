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
    const OFFSET_FOR_EXECUTE_GET_MEMBERS = 24;
    const OFFSET_FOR_EXECUTE_GET_MEMBERS_LIMIT = 1200;

    public function Execute()
    {
        set_time_limit(180);
        $this->now = new DateTimeWrapper(date( 'Y-m-d H:i:s',time()));
//        error_reporting(0);
        $barters_for_search = BarterEventFactory::Get( array( 'status' => 3 ), null, 'tst' );

        $this->search_for_posts( $barters_for_search );


        foreach( $barters_for_search as $barter ) {
            if( $barter->status == 3 ) {
                echo '213123';
                continue;
            }
            self::count_users_from_barter( $barter );
            StatPublics::update_population( $barters_for_search );

        }

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
                        } else {


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

    public function count_users_from_barter( BarterEvent $barter_event )
    {
        $result = 0;
        $offset = 0;
        $new_users = abs( $barter_event->end_subscribers - $barter_event->start_subscribers ) + 60;
        //перебираем новых юзеров паблика, проверяем, пришли ли они из паблика-рекламоразместителя
        $total_subscribers = 0;
        while( $new_users > 0 ) {
            if ( $offset > self::OFFSET_FOR_EXECUTE_GET_MEMBERS_LIMIT ) {
                $result = 0;
                break;
            }
            $code =    'var s=API.groups.getMembers({count: 24,gid: ' . $barter_event->target_public . ',sort: "time_desc",offset: ' . $offset . '});
                        var uids=s.users;
                        var cross_users_count=0;
                        var i=0;
                        while(i<uids.length){
                            var uid=uids[i];
                            cross_users_count = cross_users_count + API.groups.isMember({"gid": ' . $barter_event->barter_public . ', "uid": uid});
                            i=i+1;
                        };
                        return {"cross_users_count":cross_users_count, "users": uids};';

            for(  $try = 0; $try < 3; $try++ ) {
                $res = VkHelper::api_request( 'execute', array('code' => $code), 0 );
                if( !isset($res->error ))
                    break;
                sleep( VkHelper::PAUSE);
            }
            if( isset($res->error)) {
                die($res);
            }
            $check = array_intersect($barter_event->init_users, $res->users );
            if( !empty( $check )) {
                echo 'отсечка по юзерам<br>';
                $first_intersection  = $this->get_first_intersection($res->users, $barter_event->init_users );
                $total_subscribers  += $first_intersection;
                $result             += $first_intersection;
                break;
            }
            $total_subscribers += 24;
            $result += $res->cross_users_count;

            sleep( 1 );
            $offset += ( $new_users - self::OFFSET_FOR_EXECUTE_GET_MEMBERS > 0 ) ?  self::OFFSET_FOR_EXECUTE_GET_MEMBERS : $new_users;
            $new_users -=  self::OFFSET_FOR_EXECUTE_GET_MEMBERS;
        }
        $barter_event->skiped_subscribers  = $total_subscribers;
        $barter_event->neater_subscribers  = $result;
    }

    //возвращает число отсутствующих во втором массиве lmn первого массива( до первого совпадения )
    private function get_first_intersection( $main_array, $search_array )
    {
        $lenght = count( $main_array );
        $search_array = array_flip( $search_array );
        for( $i = 0; $i < $lenght; $i++) {
            if( isset( $search_array[$main_array[$i]] )) {
                return $i;
            }
        }
        return $lenght;
    }
}