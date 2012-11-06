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
              $barter_events_res[] = array(
                  'published_at'  =>  $publics_data[ $barter_event->barter_public ],
                  'ad_public'     =>  $publics_data[ $barter_event->target_public ],
                  'posted_at'     =>  $publics_data->posted_at,
                  'deleted_at'    =>  $publics_data->deleted_at,
                  'overlaps'      =>  explode(',', $publics_data->overlaps),
                  'subscribers'   =>  $publics_data->end_subscribers - $publics_data->start_subscribers,
                  'visitors'      =>  $publics_data->end_visitors    - $publics_data->start_visitors
              );
          }
          return $barter_events_res;
      }



  }