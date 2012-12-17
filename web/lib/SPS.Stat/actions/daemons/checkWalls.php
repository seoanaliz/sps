<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 21:06
 * To change this template use File | Settings | File Templates.
 */
class CheckWalls
{

    private $walls;
    const time_shift = 0;

    public function Execute()
    {
//        error_reporting(0);
        set_time_limit( 0 );

        if ( date('H') == 1 && date('i') < 15 ) {
            //установка новых заданных бартеров
//            $this->temp_barter_creater();
            //создание новых событий активных мониторов
            //допустим, ставим монитор на неделю. нам нужно мониторить все эти события, а не только первое.
//            $this->refresh_monitrs();
        }
        else
            echo 'not now!';
        $this->kill_overtimed();
        $this->turn_on_search();

        $barters_for_search = BarterEventFactory::Get( array( '_status' => 2 ), null, 'tst' );
        $search_results = $this->wall_search( $barters_for_search );
        $search_results = $this->get_population( $search_results );

        foreach( $barters_for_search as $barter_event )
        {
            if( isset( $search_results[ $barter_event->barter_event_id ])) {
                $barter_event->posted_at  =   date('Y-m-d H:i:s', $search_results[ $barter_event->barter_event_id ]['time']);
                $barter_event->post_id    =   $search_results[ $barter_event->barter_event_id ]['post_id'];
                $barter_event->status     =   3;
                $barter_event->start_visitors   =   $search_results[ $barter_event->barter_event_id ]['start_visitors'];
                $barter_event->start_subscribers     =   $search_results[ $barter_event->barter_event_id ]['start_subscribers'];
                $barter_event->detected_at = date( 'Y-m-d H:i:s', time());
            }
        }
        BarterEventFactory::UpdateRange( $barters_for_search, null, 'tst' );
    }

    public function kill_overtimed()
    {
        //ищем просроченные
        $events  = BarterEventFactory::Get( array( '_stop_search_atLE' => date('Y-m-d H:i:s',time() + self::time_shift + StatBarter::TIME_INTERVAL),'_status' => 2 ), null, 'tst' );
        foreach( $events as $event)
            $event->status = 5;
        BarterEventFactory::UpdateRange( $events, null, 'tst' );
    }

    public function turn_on_search()
    {
        //ищем записи, которые включаются в поиск
        $events  = BarterEventFactory::Get( array( '_start_search_atLE' => date('Y-m-d H:i:s',time() + self::time_shift + StatBarter::TIME_INTERVAL), '_status' => 1 ), null, 'tst' );
        foreach( $events as $event)
            $event->status = 2;
        BarterEventFactory::UpdateRange( $events, null, 'tst' );
    }

    public function wall_search( $publics )
    {
        $barters   = array();
        $ids_array = array();
        foreach( $publics as $barter_event )
            $ids_array[] = $barter_event->barter_public;

        $walls = StatPublics::get_public_walls_mk2( $ids_array );
        foreach( $publics as $barter_event ) {
            if( !isset( $walls[$barter_event->barter_public ])) {
                //todo логирование
                continue;
            }
            foreach( $walls[ $barter_event->barter_public ] as $post ) {
                if( $post->date < $barter_event->start_search_at->format('U')) {
                    echo 'слишком старые посты<br>';
                    break;
                }

                $barter_post = $this->find_barter( $post->text, $barter_event->search_string, $barter_event->target_public );
                //если в тексте есть вики ссылка, или это репост с нашего паблика
                if ( $barter_post
                    || ( isset( $post->copy_owner_id ) && ltrim( $post->copy_owner_id, '-' ) == $barter_event->target_public )) {
                    $barters[ $barter_event->barter_event_id ] = array(
                        'time'      =>  $post->date,
                        'post_id'   =>  $post->id ,
                        'target_id' =>  trim( $barter_event->target_public )
                    );
                }
            }
        }
        return $barters;
    }

    public function find_barter( $search_string, $public_shortname, $public_id )
    {
        $preg_filter = '/(\[\s?(?:club|public)' . $public_id . '\s?\|)/';
        if ( !preg_match( $preg_filter , $search_string ))
            if( !preg_match( '/(\[\s?' . $public_shortname . '\s?\|)/', $search_string))
                return false;
        return true;
    }

    public function get_population( $publics )
    {
        foreach( $publics as &$public ) {
            $now =  time();
            $id = $public[ 'target_id' ];

            $res = StatPublics::get_visitors_from_vk( $id, $now, $now);
            $public['start_visitors'] =  $res[ 'visitors' ];
            sleep(0.3);

            $res = VkHelper::api_request( 'groups.getMembers', array( 'gid' => $id, 'count' => 1 ), 0 );

            $public[ 'start_subscribers' ] = $res->count;
            sleep(0.3);
        }

        return $publics;
    }

    public function get_search_array()
    {
        $our_publics = StatPublics::get_our_publics_list();
    }

    //омг
    public function temp_barter_creater()
    {
        $our_array = array(
             35806721
            ,35807199
            ,36959959
            ,35807148
            ,36621513
        );

        $not_our_array = array(
             42092461
            ,33769500
            ,24313746
            ,28981879
            ,39441344
            ,35238813
            ,23616160
            ,35683607
            ,30953300
            ,30360552
            ,34522398
            ,36806640
            ,25714310
            ,34188307
            ,40182105
            ,30057637
            ,33106344
        );

        foreach( $our_array as $oid ) {
            foreach( $not_our_array as $noid ) {
                $now = time();
                $check = BarterEventFactory::Get(
                    array(
                     '_barter_public'       =>  $noid
                    ,'_target_public'       =>  $oid
                    ,'_created_atGE'        =>  date( 'Y-m-d 00:00:01', $now )
                    ,'_status' => array(1,2,3,4)
                    )
                );
                if( !empty( $check )) {
                    print_r( $check );
                    return true;
                }
                $info = $this->get_page_name( array( $oid, $noid ));
                $barter_event = new BarterEvent();
                $barter_event->barter_public =  $info['barter']['id'];
                $barter_event->target_public =  $info['target']['id'];
                $barter_event->status        =  1;
                $barter_event->search_string =  $info['target']['shortname'];
                $barter_event->barter_type   =  1;
                $barter_event->start_search_at =  date( 'Y-m-d H:i:s', $now );
                $stop_looking_time           = date( 'Y-m-d 23:59:59', $now );
                $barter_event->stop_search_at  =  $stop_looking_time;
                $barter_event->standard_mark = true;
                $barter_event->created_at    = date ( 'Y-m-d H:i:s', $now );
                $barter_event->creator_id    = '-1';
                $barter_event->groups_ids    = array(1,2,3,4,5);
                BarterEventFactory::Add( $barter_event , array( BaseFactory::WithReturningKeys => true ), 'tst' );
            }
        }
    }

    public function get_page_name( $urls )
    {
        $search = array( '/^(club)/', '/^(public)/');

        $query_line = array();
        foreach( $urls as $url ) {
            $url = parse_url( $url );
            $url = ltrim( $url['path'], '/' );
            $query_line[] = preg_replace( $search, '', $url );
        }

        $info = StatPublics::get_publics_info( $query_line );
        return  array( 'target' =>  reset( $info ),
            'barter' =>  end( $info ));
    }

    public static function refresh_monitors()
    {
        $now = date( 'Y-m-d H:i:s', time());
        $check = BarterEventFactory::Get(
            array(
                 '_created_atGE'    => date( 'Y-m-d 00:00:01', time())
                ,'standard_mark'    => true
            )
        );

        if( !empty( $check ))
            return;

        $active_monitors = BarterEventFactory::Get( array( 'status' => 2 ));
        $new_monitors = array();
        foreach( $active_monitors as $monitor ) {
            $new_monitors[]          = clone $monitor;
            $monitor->standard_mark  = false;
            $monitor->status         = 5;
            $monitor->stop_search_at = $now;
        }

        BarterEventFactory::AddRange( $new_monitors );
        BarterEventFactory::UpdateRange( $active_monitors );
    }
}