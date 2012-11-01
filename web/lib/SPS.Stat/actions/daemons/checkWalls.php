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
    public function Execute()
    {
//        error_reporting(0);
        $this->kill_overtimed();
        $this->turn_on_search();

        $barters_for_search = BarterEventFactory::Get( array('_status' => 2 ), null, 'tst' );
        $search_results = $this->wall_search( $barters_for_search );

        $search_results = $this->get_population( $search_results );
        print_r($search_results);
        foreach( $barters_for_search as $barter_event )
        {
            if( isset( $search_results[ $barter_event->barter_event_id ])) {
                $barter_event->posted_at  =   date('Y-m-d H:m:s', $search_results[ $barter_event->barter_event_id ]['time']);
                $barter_event->post_id    =   $search_results[ $barter_event->barter_event_id ]['post_id'];
                $barter_event->status     =   3;
                $barter_event->start_visitors   =   $search_results[ $barter_event->barter_event_id ]['start_visitors'];
                $barter_event->start_subscribers     =   $search_results[ $barter_event->barter_event_id ]['start_subscribers'];
            }
        }
        BarterEventFactory::UpdateRange( $barters_for_search, null, 'tst' );
    }

    public function kill_overtimed()
    {
        //ищем просроченные
        $events  = BarterEventFactory::Get( array( '_stop_search_atLE' => date('Y-m-d H:m:s',time() + 10800 + StatBarter::TIME_INTERVAL),'_statusNE' => 5 ), null, 'tst' );
        foreach( $events as $event)
            $event->status = 5;
        BarterEventFactory::UpdateRange( $events, null, 'tst' );
    }

    public function turn_on_search()
    {
        //ищем записи, которые включаются в поиск
        $events  = BarterEventFactory::Get( array( '_start_search_atLE' => date('Y-m-d H:m:s',time() + 10800 + StatBarter::TIME_INTERVAL), '_status' => 1 ), null, 'tst' );
        foreach( $events as $event)
            $event->status = 2;
        BarterEventFactory::UpdateRange( $events, null, 'tst' );
    }

    public function wall_search( $publics )
    {
        $barters = array();
        $publics_chunks = array_chunk( $publics, 25 );

        foreach( $publics_chunks as $public_chunk ) {

            $res = StatPublics::get_publics_walls( $public_chunk );
            $public = reset( $public_chunk );
            //обработка стенок, поиск нужного поста

            foreach( $res as $public_wall ) {
                foreach( $public_wall as $post ) {
                    print_r($post);
                    $barter_post = $this->find_barter( $post->text, $public->search_string, $public->target_public );
                    //если в тексте есть вики ссылка, или это репост с нашего паблика
                    if ( $barter_post
                        || ( isset( $post->copy_owner_id ) && ltrim( $post->copy_owner_id, '-' ) == $public->target_public )) {
                        $barters[ $public->barter_event_id ] = array(
                            'time'      =>  $post->date,
                            'post_id'   =>  $post->id,
                            'target_id' =>  trim( $public->target_public )
                        );
                    }
                }
                $public = next( $public_chunk );
            }
        }
        print_r( $barters );
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
            print_r($res);
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



}