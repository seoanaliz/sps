<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.10.12
 * Time: 19:45
 * To change this template use File | Settings | File Templates.
 */
class StatBarter
{
    /** начинаем и заканчиваем поиск поста с этим добавочным интервалом */
    const TIME_INTERVAL = 3600;

    /** какое время ищем бартерный пост*/
    const DEFAULT_SEARCH_DURATION = 86400;

    public static function form_response( $query_result )
    {
        $request_line = '';
        // строкa для запроса данных о пабликах
        foreach( $query_result as $barter_event ) {
            $request_line .= $barter_event->barter_public . ',' . $barter_event->target_public . ',';
        }

        $request_line = rtrim( $request_line, ',' );
        $publics_data = StatPublics::get_publics_info( $request_line );

        $barter_events_res = array();
        foreach( $query_result as $barter_event ) {

            $overlaps = isset( $barter_event->barter_overlaps ) ? explode( ',', $barter_event->barter_overlaps ) : array();
            $overlaps = !empty( $overlaps ) ? ( $overlaps[0] - $barter_event->posted_at->format('U'))  : 0;
            if( abs($overlaps) > 20000) $overlaps = 0;
            $posted_at  = isset( $barter_event->posted_at ) ? $barter_event->posted_at->format('U') : 0;
            $deleted_at = isset( $barter_event->deleted_at ) ? $barter_event->deleted_at->format('U') : $posted_at + 3600;
            $lifetime = ( $posted_at && $deleted_at ) ? $deleted_at - $posted_at : 0;
            if ( $lifetime )
                $lifetime = $lifetime;


            $barter_events_res[] = array(
                'report_id'     =>  $barter_event->barter_event_id,
                'published_at'  =>  $publics_data[ $barter_event->barter_public ],
                'ad_public'     =>  $publics_data[ $barter_event->target_public ],
                'posted_at'     =>  $posted_at,
                'detected_at'   =>  isset( $barter_event->posted_at ) ? $barter_event->posted_at->format('U') : 0,
                'deleted_at'    =>  $lifetime,
                'start_search_at' => $barter_event->start_search_at->format('U'),
                'stop_search_at' =>  $barter_event->stop_search_at->format('U') - $barter_event->start_search_at->format('U'),
                'overlaps'      =>  $overlaps,
                'subscribers'   =>   ( $barter_event->end_subscribers && $barter_event->start_subscribers )?
                    ( $barter_event->end_subscribers - $barter_event->start_subscribers ) : 0,
                'visitors'      =>  ( $barter_event->start_visitors && $barter_event->end_visitors ) ?
                    ( $barter_event->end_visitors    - $barter_event->start_visitors ) : 0,
                'status'        =>   $barter_event->status,
                'active'        =>   in_array( $barter_event->status, array(1,2,3)) ? true : false
            );
        }
        return $barter_events_res;
    }

    //ищет похожие события.
    public static function get_concrete_events( $target_public_id, $barter_public_id, $active = 0, $stanard = 0 )
    {
        switch( $active ) {
            //все( кроме удаленных )
            case 0:
               $status_array = array(1,2,3,4,5);
                break;
            case 1:
                //активные
                $status_array = array(1,2,3);
                break;
            case 2:
                //неактивные
                $status_array = array(4,5);
                break;
            default:
                $status_array = array(1,2,3,4,5);
        }

        $search = array(
            '_barter_public' => $barter_public_id
           ,'_target_public' => $target_public_id
           ,'_status'        => $status_array
        );
        if( $stanard )
            $search['_standard_markE'] = true;

        return BarterEventFactory::Get( $search, null, 'tst' );
    }

    public static function get_page_name( $urls )
    {
        $search = array( '/^(club)/', '/^(public)/');

        $query_line = array();
        foreach( $urls as $url ) {
            $url = parse_url( $url );
            $url = ltrim( $url['path'], '/' );
            $query_line[] = preg_replace( $search, '', $url );
        }
        if ( count( $query_line ) != 2 )
            return array();
        $info = StatPublics::get_publics_info( $query_line );
        return  array( 'target' =>  reset( $info ),
            'barter' =>  end( $info ));
    }
}