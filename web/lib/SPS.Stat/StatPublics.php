<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
//    Package::Load( 'SPS.Stat' );

    class StatPublics
    {
        const FAVE_PUBLS_URL = 'http://vk.com/al_fans.php?act=show_publics_box&al=1&oid=';
        public static $exception_publics_array = array(
         25678227
        ,26776509
        ,43503789
        ,346191
        ,33704958
        ,38000521
        ,1792796
        ,27421965
        ,34010064
        ,25749497
        ,35807078
        ,25817269
        );

        public static function get_id_by_shortname( $shortname )
        {
            $params = array( 'gids'  => $shortname );
            $res = VkHelper::api_request( 'groups.getById', $params );
            return $res[0]->gid;
        }

        public static function get_our_publics_list()
        {
            $publics = TargetFeedFactory::Get();
            //массив пабликов, которые не надо включать в сбор/отбражение данных
            $res = array();
            foreach ( $publics as $public ) {
                if( $public->type != 'vk' || in_array( $public->externalId, self::$exception_publics_array ))
                    continue;

                $a['id']    = $public->externalId;
                $a['title'] = $public->title;
                $a['sb_id'] = $public->targetFeedId;
                $res[] = $a;
            }
            return $res;
        }

        public static function get_publics_info( $public_ids )
        {
            if( is_array( $public_ids ))
                $public_ids = implode( ',', $public_ids );
            $result = array();
            $res = VkHelper::api_request( 'groups.getById', array( 'gids' => $public_ids ), 0 );
            if( isset( $res->error ))
                return false;
            $result = array();
            foreach( $res as $public ) {
                $result[ $public->gid ] = array(
                    'id'    =>  $public->gid,
                    'ava'   =>  $public->photo,
                    'name'  =>  $public->name,
                    'link'  =>  'http://vk.com/public' . $public->gid,
                    'shortname' => $public->screen_name
                );
            }

            return $result;
        }

        public static function get_last_update_time()
        {
            $sql = 'SELECT MAX(time) FROM ' . TABLE_STAT_PUBLICS_POINTS;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $ds->Next();

            return $ds->getValue('max', TYPE_INTEGER);
        }

        public static function get_public_users( $public_id, $data_base, $offset = 0 )
        {
            $public_id = +$public_id;
            $offset    = +$offset;
            $sql = "INSERT INTO publics VALUES (@public_id, '{0}')";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( $data_base ));
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->Execute();

            while (1) {
                $values = '';
                $code = '';
                $return = "return{";
                for ( $i = 0; $i < 25; $i++ ) {
                    $code   .= "var a$i = API.groups.getMembers({\"gid\":$public_id, \"count\":1000, \"offset\":$offset }) ;";
                    $return .= "\"a$i\":a$i,";
                    $offset += 1000;
                }
                $code .= trim($return, ',') . "};";
                $res = VkHelper::api_request( 'execute', array( 'code' => $code ));
                if ( count( $res->a0->users ) == 0 )
                    break;
                foreach( $res as $query_reuslt ) {
                    $values .= implode( ',', $query_reuslt->users ) . ',';
                }
//                echo '<br>' . count( explode( ',', $values)) . '<br>';
                sleep(0.4);
                $values = "{" . trim( $values, ',' ) . "}";

                $sql = 'UPDATE publics SET "vkIds" = "vkIds" + @array WHERE "publicId" = @public_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
                $cmd->SetString ( '@array',     $values );
                $cmd->SetInteger( '@public_id', $public_id );
                $cmd->Execute();
            }
        }

        public static function get_50k( $start = 0, $stop = 1000000000 )
        {
            $sql = 'DELETE FROM publics WHERE "publicId" = @stop';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@stop',  $stop );
            $cmd->Execute();

            $sql = 'SELECT vk_id FROM ' . TABLE_STAT_PUBLICS . '
                    WHERE quantity > 50000 AND vk_id >= @start AND vk_id <= @stop
                    ORDER BY vk_id DESC';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@start', $start );
            $cmd->SetInteger( '@stop',  $stop  );
            $ds = $cmd->Execute();

            $res = array();

            while( $ds->Next()) {
                $res[] = $ds->GetValue( 'vk_id' );
            }
            return $res;
        }

        public static function get_distinct_users()
        {
            $sql = 'SELECT DISTINCT * FROM ' . TABLE_TEMPL_USER_IDS;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $ds =  $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[] = $ds->GetValue( 'id', TYPE_INTEGER );
            }
            return $res;
        }

        //собирает топ 5 пабликов пользователей
        public static function collect_fave_publics( $users_array )
        {
            set_time_limit(0);
            $fave_array = array();
            $i = 0;
            $url_array = array();
            foreach( $users_array as $user ) {
                $url_array[] = self::FAVE_PUBLS_URL . $user;
                $i++;
                if ( $i == 20 ) {
//                    echo '1 <br>';
                    $res = array();
                    VkHelper::multiget( $url_array, $res );
//                    print_r($res);
                    foreach( $res as $page ) {
                        $matches = array();
//                        $public_list = file_get_contents( self::FAVE_PUBLS_URL . $user );
                        $page = explode( 'setUpTabbedBox', $page );
                        preg_match_all( '/\/g(\d{2,14})\//', $page[0], $matches );
                        $fave_array = array_merge( $fave_array, reset( array_chunk( $matches[1], 7 )));
                    }

                    $values = implode( '),(', $fave_array );
                    if ( $values ) {
                        $sql = 'INSERT INTO ' . TABLE_TEMPL_PUBLIC_SHORTNAMES . ' VALUES (' . $values . ')';
                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
                        $cmd->ExecuteNonQuery();
                    }
                    $i = 0;
                    $fave_array = array();
                    $url_array = array();
                }
                sleep( 0.1 );
            }
        }

        public static function truncate_table( $table )
        {
             $sql = 'TRUNCATE TABLE ' .$table ;
             $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
             return $cmd->ExecuteNonQuery();
         }

        //sh table
        public static function get_intersections( $first_public, $second_public )
        {
            $sql = 'SELECT icount(
                     ( SELECT "vkIds" FROM "publics" WHERE "publicId" = @first_public )
                      &
                     ( SELECT "vkIds" FROM "publics" WHERE "publicId" = @second_public ))';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@first_public',   $first_public  );
            $cmd->SetInteger( '@second_public',  $second_public );
            $ds = $cmd->Execute();
            $ds->Next();
            return $ds->GetInteger( 'icount');
        }

        //sh table
        public static function get_table()
        {
            $publics = self::get_50k();
            $count   = count( $publics );
            $res = array();
            $a = microtime(1);
            for ( $i = 0; $i < $count; $i++ ) {
                for ( $j = $i + 1; $j < $count; $j++ ) {
                    $res[$i][$j] = self::get_intersections( $publics[$i], $publics[$j] );
                    $res[$j][$i] = $res[$i][$j];
                    echo 'между ' . $publics[$i] . ' и ' . $publics[$j] . ' ' . $res[$j][$i] .' пересечений<br>';
                }

                echo '<br>';
                die( $a - microtime(1));
            }
//            print_r($res);
            return $res;
        }

        public static function get_sb_public_ids( $vk_public_ids )
        {
            if ( is_array( $vk_public_ids ))
                $vk_public_ids = implode( ',', $vk_public_ids );
            $vk_public_ids = '{' . $vk_public_ids . '}';
            $sql = 'SELECT
                        "externalId",
                        "targetFeedId"
                    FROM
                        "targetFeeds"
                    WHERE
                        "externalId" = ANY(@public_ids)
                    ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString ( '@public_ids',     $vk_public_ids );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[ $ds->GetInteger( 'externalId')]    =  $ds->GetInteger( 'targetFeedId' );
            }
            return $res;
        }

        //сохраняет настройки для oadmins
        public static function save_conf( $c1,$c2,$c3,$c4,$lv )
        {
            $sql = 'UPDATE oadmins_conf SET complicate = @1,
                                            reposts    = @2,
                                            rel_mark   = @3,
                                            overposts  = @4,
                                            price      = @lv ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );

            $cmd->SetFloat('@1'   , $c1);
            $cmd->SetFloat('@2'   , $c2);
            $cmd->SetFloat('@3'   , $c3);
            $cmd->SetFloat('@4'   , $c4);
            $cmd->SetInteger('@lv', $lv);

            $cmd->Execute();
        }

        public static function get_conf()
        {
            $sql = 'SELECT * FROM oadmins_conf';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $ds->Next();
            return array(
                'c1_old'    =>  $ds->GetFloat( 'complicate' ),
                'c2_old'    =>  $ds->GetFloat( 'reposts'    ),
                'c3_old'    =>  $ds->GetFloat( 'rel_mark'   ),
                'c4_old'    =>  $ds->GetFloat( 'overposts'  ),
                'lval_old'  =>  $ds->GetFloat( 'price'      ),
            );
        }

        //собирает инфу о постах(рекламных, авторских или из источников) из sb за период
        public static function get_public_posts( $public_sb_id, $search_param, $time_from, $time_to )
        {
            //выбор, какие посты ищем:
            switch ( $search_param ) {
                case 'authors':
                    $type = "";
                    $operator = ' = ';
                    $add = ' OR "editor" is not null ';
                    break;
                case 'sb':
                    $type = " AND c.type<>'ads ' ";
                    $operator = ' <> ';
                    $add = ' AND "editor" is null ';
                    break;
                //по умолчанию - рекламные
                default:
                    $type = " AND c.type='ads' ";
                    $operator = ' <> ';
            }
            if ( !$time_to )
                $time_to = time();
            $sql = '
            SELECT
                COUNT(*),
                avg("externalLikes") as avg_likes,
                avg("externalRetweets") as avg_reposts
            FROM
                  "articles" as a LEFT JOIN "articleQueues" as b
                  USING("articleId") LEFT JOIN "sourceFeeds" as c
                  USING("sourceFeedId")
            WHERE
                  b."sentAt" > @time_from
              AND b."sentAt" < @time_to
              AND ( "sourceFeedId" ' . $operator . ' -1
                ' .$add .')
              AND b."targetFeedId" = @targetFeedId
              AND b."externalId" IS NOT NULL'
              . $type ;

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( '' ));
            $cmd->SetInteger( '@targetFeedId', $public_sb_id );
            $cmd->SetString ( '@time_from', date('Y-m-d H:i:00', $time_from ));
            $cmd->SetString ( '@time_to',   date('Y-m-d H:i:00', $time_to ));
            $ds = $cmd->Execute();
            $ds->next();
            $res = array(
               'likes'      =>  round( $ds->GetFloat( 'avg_likes' )),
               'reposts'    =>  round( $ds->GetFloat( 'avg_reposts' )),
               'count'      =>  round( $ds->GetFloat( 'count' )),
            );
            return $res;
        }

        public static function get_average($public_sb_id, $time_from, $time_to )
        {
            $sql = 'SELECT
                        COUNT(*),
                        avg("externalLikes") as avg_likes,
                        avg("externalRetweets") as avg_reposts
                    FROM
                        "articles" as a LEFT JOIN
                        "articleQueues" as b USING("articleId") LEFT JOIN
                        "sourceFeeds" as c USING("sourceFeedId")
                    WHERE
                        b."sentAt" > @time_from
                        AND b."sentAt" < @time_to
                        AND b."targetFeedId" = 4
                        AND b."externalId" IS NOT NULL';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( '' ));
            $cmd->SetInteger( '@targetFeedId', $public_sb_id );
            $cmd->SetString ( '@time_from', date('Y-m-d H:i:00', $time_from ));
            $cmd->SetString ( '@time_to',   date('Y-m-d H:i:00', $time_to ));
            $ds = $cmd->Execute();
            $ds->next();
            $res = array(
                'likes'      =>  round( $ds->GetFloat( 'avg_likes' )),
                'reposts'    =>  round( $ds->GetFloat( 'avg_reposts' )),
                'count'      =>  round( $ds->GetFloat( 'count' )),
            );
            return $res;
        }

        public static function get_average_visitors( $sb_id, $time_from, $time_to )
        {
            $public = TargetFeedFactory::Get( array( 'targetFeedId' => $sb_id ));

            $sql = 'SELECT avg(visitors)
                      FROM ' . TABLE_STAT_PUBLICS_POINTS . '
                      WHERE time >= @time_from
                            AND time <= @time_to
                            AND id = @public_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString( '@time_from', date('Y-m-d', $time_from ));
            $cmd->SetString( '@time_to',   date('Y-m-d', $time_to ));
            $cmd->SetInteger( '@public_id', $public[$sb_id]->externalId );
            $ds = $cmd->Execute();
            if ( $ds->GetSize() ) {
                $ds->Next();
                return $ds->GetInteger( 'avg' );
            }
            return 0;
        }

        public static function get_avg_subs_growth( $sb_id, $time_from, $time_to )
        {
            $public = TargetFeedFactory::Get( array( 'targetFeedId' => $sb_id ));

            $sql = 'SELECT avg(visitors)
                          FROM ' . TABLE_STAT_PUBLICS_POINTS . '
                          WHERE time >= @time_from
                                AND time <= @time_to
                                AND id = @public_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetString( '@time_from', date('Y-m-d', $time_from ));
            $cmd->SetString( '@time_to',   date('Y-m-d', $time_to ));
            $cmd->SetInteger( '@public_id', $public[$sb_id]->externalId );
            $ds = $cmd->Execute();
            if ( $ds->GetSize() ) {
                $ds->Next();
                return $ds->GetInteger( 'avg' );
            }
            return 0;
        }

        public static function get_views_visitors_from_base( $sb_id, $time_from, $time_to )
        {
            $public = TargetFeedFactory::Get( array( 'targetFeedId' => $sb_id ));
            $sql = 'SELECT views,visitors,avg()
                    FROM stat_publics_50k_points
                    WHERE   time >= @time_from
                            AND time <= @time_to
                            AND id = @public_id
                    ORDER BY time';
            $cmd = new SqlCommand( $sql,    ConnectionFactory::Get( 'tst' ));
            $cmd->SetString( '@time_from',  date('Y-m-d', $time_from ));
            $cmd->SetString( '@time_to',    date('Y-m-d', $time_to ));
            $cmd->SetInteger( '@public_id', $public[$sb_id]->externalId );
            $ds = $cmd->Execute();

            $days = $ds->GetSize();

            $requested_days = round(( $time_to - $time_from ) / 86400) + 1;
            //проверка на наличие данных на этот период в бд. если нет - запрос в контакт
            if( $days != $requested_days )
                return false;
            $diff_views =   0;
            $diff_viss  =   0;
            $views      =   0;
            $visitors   =   0;
            $temp_viss  =   0;
            while( $ds->Next()) {
                if ( isset( $temp_views )) {
                    $diff_views +=  $ds->GetInteger( 'views' )    - $temp_views;
                    $diff_viss  +=  $ds->GetInteger( 'visitors' ) - $temp_viss;
                }

                $temp_views =   $ds->GetInteger( 'views' );
                $temp_viss  =   $ds->GetInteger( 'visitors' );
                $views      +=  $temp_views;
                $visitors   +=  $temp_viss;
            }
            return  array(
                'views'         =>  round( $views       / $days ),
                'visitors'      =>  round( $visitors    / $days ),
                'vievs_grouth'  =>  round( $diff_views  / ( $days - 1 )),
                'vis_grouth'    =>  round( $diff_viss   / ( $days - 1 )),
            );

        }

        public static function get_views_visitors_from_vk( $public_id, $time_from, $time_to )
        {
            $public = TargetFeedFactory::Get( array( 'externalId' => $public_id ));
            if ( !empty( $public )) {
                $public = reset( $public );
//                $publisher = TargetFeedPublisherFactory::Get( array( 'targetFeedId' => $public->targetFeedId ));
//                $publisher = reset( $publisher );
            }

            $params = array(
                'gid'           =>  $public_id,
                'date_from'     =>  date( 'Y-m-d', $time_from ),
                'date_to'       =>  date( 'Y-m-d', $time_to )
            );
//            if ( isset( $publisher->publisher->vk_token ))
//                $params['access_token']  =  $publisher->publisher->vk_token;

            $res = VkHelper::api_request( 'stats.get', $params, 0 );
            if ( !empty ( $res->error))
                return false;
            $connect = ConnectionFactory::Get( 'tst' );
            foreach( $res as $day ) {
                StatPublics::save_view_visitor( $public_id, $day->views, $day->visitors, $day->day, $connect );
            }
            sleep(0.3);
        }

        public static function get_average_rate( $sb_id, $time_from, $time_to ) {

            if ( !$time_to )
                $time_to = time();
            $sql = 'SELECT
                        b.rate
                    FROM
                      "articleQueues" AS a INNER JOIN articles AS b USING("articleId")
                    WHERE
                        a."sentAt" > @time_from
                    AND a."sentAt" < @time_to
                    AND a."targetFeedId" = @targetFeedId
                    AND b.rate > 0
                    ';

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( '' ));
            $cmd->SetInteger( '@targetFeedId', $sb_id );
            $cmd->SetString ( '@time_from', date( 'Y-m-d H:i:00', $time_from ));
            $cmd->SetString ( '@time_to',   date( 'Y-m-d H:i:00', $time_to ));
            $ds = $cmd->Execute();

            $rate = 0;

            while( $ds->next()) {
                $tmp_rate = $ds->GetValue( 'rate' );
                $rate += $tmp_rate < 100 ? $tmp_rate : 100;
            }
            if ( $rate )
                return round( $rate / $ds->GetSize());
            return 0;
        }

        public static function save_view_visitor( $public_id, $views, $visitors, $date, $connect )
        {
            $sql = 'select * from
                stat_publics_50k_points
            WHERE
                id=@public_id
                AND time=@date';
            $cmd = new SqlCommand( $sql, $connect );
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->SetString ( '@date',      $date );
            $ds = $cmd->Execute();

            if ( $ds->GetSize()) {
                $sql = 'UPDATE
                        stat_publics_50k_points
                    SET
                        visitors=@visitors,
                        views   =@views
                    WHERE
                        id=@public_id
                        AND time=@date';
                $cmd = new SqlCommand( $sql, $connect );
                $cmd->SetInteger( '@public_id', $public_id );
                $cmd->SetInteger( '@visitors',  $visitors );
                $cmd->SetInteger( '@views',     $views );
                $cmd->SetString ( '@date',      $date );

                $cmd->Execute();
            } else {
                $sql = 'INSERT INTO
                        stat_publics_50k_points
                    VALUES(
                           @public_id,
                           @date,
                           0,
                           @visitors,
                           @views
                    )';
                $cmd = new SqlCommand( $sql, $connect );
                $cmd->SetInteger( '@public_id', $public_id );
                $cmd->SetInteger( '@visitors',  $visitors );
                $cmd->SetInteger( '@views',     $views );
                $cmd->SetString ( '@date',      $date );
                $cmd->Execute();
            }

        }

        //РІРѕР·РІСЂР°С‰Р°РµС‚ СЃС‚РµРЅС‹ РґРѕ 25 РїР°Р±Р»РёРєРѕРІ
        public static function get_publics_walls( $barter_events_array )
        {
            $code = '';
            $return = "return{";
            //Р·Р°РїСЂР°С€РёРІР°РµРј СЃС‚РµРЅС‹ РїР°Р±Р»РёРєРѕРІ РїРѕ 25 РїР°Р±Р»РёРєРѕРІ, 15 РїРѕСЃС‚РѕРІ
            $i = 0;
            foreach( $barter_events_array as $public ) {
                $id = trim( $public->barter_public );
                $code   .= 'var id' . $i . ' = API.wall.get({"owner_id":-' . $id . ',"count":15 });';
                $return .=  "\"id$i\":id$i,";
                $i++;
            }
            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute', array( 'code' => $code ), 0 );
            return $res;
        }

        public static function get_public_walls_mk2( $walls_array )
        {
            $walls = array();
            $walls_array = array_unique( $walls_array );
            $sliced_walls_array = array_chunk( $walls_array, 25 );
            foreach( $sliced_walls_array as $chunk ) {
                $code = '';
                $return = "return{";
                //Р·Р°РїСЂР°С€РёРІР°РµРј СЃС‚РµРЅС‹ РїР°Р±Р»РёРєРѕРІ РїРѕ 25 РїР°Р±Р»РёРєРѕРІ, 10 РїРѕСЃС‚РѕРІ
                $i = 0;
                foreach( $chunk as $public ) {
                    $id = trim( $public );
                    $code   .= 'var id' . $id . ' = API.wall.get({"owner_id":-' . $id . ',"count":10 });';
                    $return .=  "\"id$id\":id$id,";
                    $i++;
                }
                $code .= trim( $return, ',' ) . "};";
                $res   = VkHelper::api_request( 'execute', array( 'code' => $code ), 0 );
                if( isset( $res->error ))
                    continue;
                foreach( $res as $id => $content ) {
                    unset( $content[0] );
                    $walls[ str_replace( 'id', '', $id )] = $content;
                }
            }
            return $walls;
        }

        public static function get_visitors_from_vk( $public_id, $time_from, $time_to )
        {
//            $public = TargetFeedFactory::Get( array( 'externalId' => $public_id ));
//            if ( !empty( $public )) {
//                $public     = reset( $public );
//                $publisher  = TargetFeedPublisherFactory::Get( array( 'targetFeedId' => $public->targetFeedId ));
//                $publisher  = reset( $publisher );
//            }

            $params = array(
                'gid'           =>  $public_id,
                'date_from'     =>  date( 'Y-m-d', $time_from ),
                'date_to'       =>  date( 'Y-m-d', $time_to )
            );
            for( $i = 0; $i < 3; $i++ ) {
                sleep(0.6);
                $res = VkHelper::api_request( 'stats.get', $params, 0 );
                if ( empty ( $res->error ) && !empty( $res ))
                    break;
            }
            if ( !empty ( $res->error ) || empty( $res ))
                return false;

            return array(
                'visitors'  =>  $res[0]->visitors,
                'viewers'   =>  $res[0]->views
            );
        }

        public static function get_publics_info_from_base( $public_ids )
        {
            $public_ids = implode( ',', $public_ids );
            $sql = 'SELECT vk_id, name, ava, quantity, page
                    FROM ' . TABLE_STAT_PUBLICS . '
                    WHERE vk_id IN (' . $public_ids . ')';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $cmd->SetString( '@public_ids', '\'{' . $public_ids . '}\'' );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[ $ds->GetInteger( 'vk_id' )] = array(
                    'name'      =>   $ds->GetString ( 'name' ),
                    'ava'       =>   $ds->GetString ( 'ava' ),
                    'quantity'  =>   $ds->GetInteger( 'quantity' ),
                    'page'      =>   $ds->GetBoolean( 'page')
                );
            }
            return $res;
        }

        //проверяет изменения в пабликах(название и ава)
        public static function update_public_info( $publics, $conn )
        {
            $public_chunks = array_chunk( $publics, 500 );

            foreach( $public_chunks as $ids ) {
                $line = implode( ',', $ids );
                $res = VkHelper::api_request('groups.getById', array( 'gids' => $line ), 0);
                sleep(0.3);
                foreach( $res as $public ) {
                    //проверяет, изменяется ли название паблика. если да - записывает изменения в stat_public_audit
                    $sql = '
                    DROP FUNCTION IF EXISTS update_public_info( id integer, p_name varchar , ava varchar, page boolean);
                    CREATE FUNCTION update_public_info( id integer, p_name varchar, ava varchar, page boolean) RETURNS varchar AS $$
                    DECLARE
                        curr_name CHARACTER VARYING := \'_\';
                    BEGIN
                        SELECT name INTO curr_name FROM stat_publics_50k WHERE vk_id=$1;
                        IF( curr_name=p_name )
                        THEN
                            curr_name := \'\';
                        ELSE
                            INSERT INTO stat_public_audit(public_id,name,changed_at,act) VALUES ($1,curr_name,CURRENT_TIMESTAMP,\'name\');
                        END IF;
                        UPDATE stat_publics_50k SET name = $2, ava=$3, page=$4 WHERE vk_id=$1;
                        RETURN curr_name;
                    END
                    $$ lANGUAGE plpgsql;
                    SELECT update_public_info( @public_id, @name, @photo, @page ) AS old_name;';
                    $cmd = new SqlCommand( $sql, $conn );
                    $cmd->SetInteger( '@public_id', $public->gid );
                    $cmd->SetString(  '@name', $public->name );
                    $cmd->SetString(  '@photo', $public->photo);
                    $cmd->SetBoolean( '@page', ( $public->type == 'page' ? true : false ));
                    $cmd->Execute();
                }
            }
        }

        //проверяет, оменялось ли конкретное булево состояние паблика(active, in_search, closed)
        //возвращает true , если были изменения, false - нет
        //записывает изменения в
        public static function set_state( $public_id, $parameter, $state, $conn )
        {
            $sql =
                "DROP FUNCTION IF EXISTS set_state( id integer, column_name varchar , new_value boolean );
                CREATE FUNCTION set_state( id integer, column_name varchar , state boolean ) RETURNS boolean AS $$
                DECLARE
                old_value boolean    := 0;
                curr_state boolean := false;
                BEGIN
                    execute 'SELECT '|| column_name ||' FROM stat_publics_50k WHERE vk_id='||$1 INTO old_value;
                    IF $3=old_value THEN
                        return false;
                    ELSE
                        execute 'INSERT INTO stat_public_audit( public_id, '||$2||', changed_at,act) VALUES ( '||$1||','||$3||',CURRENT_TIMESTAMP, '''||$2||''' )';
                        execute 'UPDATE stat_publics_50k SET '||$2||' = '||$3||' WHERE  vk_id='||$1;
                        return true;
                    END IF;
                END
                $$ LANGUAGE plpgsql;
                SELECT set_state( @public_id, @name, @state) AS cnanged;";
                $cmd = new SqlCommand( $sql, $conn );
                $cmd->SetInteger( '@public_id', $public_id );
                $cmd->SetString(  '@name',      $parameter );
                $cmd->SetBoolean( '@state',     $state);
                $cmd->Execute();
        }

        public static function get_public_changes( $time_from, $time_to, $conn = 0 )
        {
            if ( !$conn )
                $conn = ConnectionFactory::Get('tst');

            $sql = 'SELECT
                        public_id, a.name as old_name, changed_at, b.name,a.act,a.active,b.active as check,a.in_search, a.closed
                    FROM '
                . TABLE_STAT_PUBLICS_AUDIT . ' as a '.
                'JOIN ' . TABLE_STAT_PUBLICS . ' as b '.
                'ON
                        public_id=vk_id
                    WHERE
                            changed_at > @time_from
                        AND changed_at < @time_to
                    ORDER BY a.act
                    ';
            $cmd = new SqlCommand( $sql, $conn );
            $cmd->SetString( '@time_from', date( 'Y-m-d H:i:s', $time_from ));
            $cmd->SetString( '@time_to', date( 'Y-m-d H:i:s', $time_to ));
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $public_id = $ds->GetInteger( 'public_id' );
                if ( isset( $res[ $public_id ] ) && $res[ $public_id  ]['act'] == 'active' ) {
                    continue;
                }
                $res[ $public_id ] = array(
                    'act'        =>  $ds->GetValue( 'act' ),
                    'old_name'   =>  $ds->GetValue( 'old_name' ),
                    'new_name'   =>  $ds->GetValue( 'name' ),
                    'in_search'  =>  $ds->GetValue( 'in_search' ),
                    'closed'     =>  $ds->GetValue( 'closed' ),
                    'active'     =>  $ds->GetValue( 'active' ),
                );
                $public_id = '';
            }
            return $res;
        }

        public static function search_public( $search_string )
        {
            //поиск id паблика
            $int_search = (int) $search_string;

            $sql = 'SELECT vk_id,ava, name,quantity,page
                    FROM ' . TABLE_STAT_PUBLICS .
                   ' WHERE
                        ( name ILIKE @search_string
                        OR vk_id = @int_search )
                        AND active IS TRUE
                        AND quantity > 50000
                    ORDER BY quantity DESC
                   ';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $cmd->SetInteger( '@int_search', $int_search );
            $cmd->SetString( '@search_string', '%' . $search_string . '%' );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                $res[] = array(
                    'id'        =>  $ds->GetInteger('vk_id'),
                    'quantity'  =>  $ds->GetInteger('quantity'),
                    'name'      =>  $ds->GetString('name'),
                    'ava'       =>  $ds->GetString('ava'),
                    'type'      =>  $ds->GetBoolean( 'page') == 't' ? 'page' : 'groupe',
                );
            }
            return $res;
        }
    }
?>
