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
              $overlaps = isset( $barter_event->overlaps ) ? explode( ',', $barter_event->overlaps ) : array();
              $barter_events_res[] = array(
                  'published_at'  =>  $publics_data[ $barter_event->barter_public ],
                  'ad_public'     =>  $publics_data[ $barter_event->target_public ],
                  'posted_at'     =>  isset( $barter_event->posted_at ) ? $barter_event->posted_at->format('U') : 0,
                  'deleted_at'    =>  isset( $barter_event->deleted_at ) ? $barter_event->deleted_at->format('U') : 0,
                  'overlaps'      =>  $overlaps,
                  'subscribers'   =>   $barter_event->end_subscribers ?
                        $barter_event->end_subscribers - $barter_event->start_subscribers : 0,
                  'visitors'      =>  $barter_event->end_visitors ?
                        $barter_event->end_visitors    - $barter_event->start_visitors : 0,
                  'status'        =>   $barter_event->status
              );
          }
          return $barter_events_res;
      }



  }