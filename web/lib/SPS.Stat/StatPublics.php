<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    new stat_tables();

    class StatPublics
    {

        const time_shift = 0;
        const FAVE_PUBLS_URL = 'http://vk.com/al_fans.php?act=show_publics_box&al=1&oid=';
        const STAT_QUANTITY_LIMIT = 30000;
        const WARNING_DATA_NOT_ACCURATE = 1;
        const WARNING_DATA_FROM_YESTERDAY = 2;
        const WARNING_DATA_ACCURATE = 3;
        //на проде - 435
        const cheapGroupId = 435;

        //массив пабликов, которые не надо включать в сбор/отбражение данных
        public static $exception_publics_array = array(
             26776509
            ,43503789
            ,346191
            ,33704958
            ,38000521
            ,1792796
            ,34010064
            ,25749497
            ,35807078
            ,25817269
            ,31554488
            ,49765084
        );

        public static $topface_beauty = array(
            25678227,
            38000449,
            35807278,
            38000423,
            38000513,
            38000540,
            42351996,
            41946825,
            42352011,
            42352024,
            42494921,
            42352077,
            42352062,
            42494824,
            42494714,
            41946847,
            42495064,
            42352154,
            42352138,
            42494794,
            41946872,
            42495143,
            42495239,
            42494936,
            41946945,
            42495024,
            41946887,
            41946921,
            42495048,
            42494987,
            41946866,
            42494848,
            42352086,
            42352120,
            42494766,
            49903343
        );

        public static function get_id_by_shortname( $shortname )
        {
            $params = array( 'gids'  => $shortname );
            $res = VkHelper::api_request( 'groups.getById', $params );
            return $res[0]->gid;
        }

        public static function get_our_publics_list( $selector = 0 )
        {
            $publics = TargetFeedFactory::Get(array('isOur' => true ));
            $res = array();
            foreach ( $publics as $public ) {
                if( $public->type != 'vk' || in_array( $public->externalId, self::$exception_publics_array ))
                    continue;
                // селектором выбираем только топфейсовские паблики(1) или только не топфесовские(2)
                if(( $selector == 1 && in_array( $public->externalId, self::$topface_beauty)) ||
                    ($selector == 2 && !in_array( $public->externalId, self::$topface_beauty)))
                    continue;
                $a['id']    = $public->externalId;
                $a['title'] = $public->title;
                $a['sb_id'] = $public->targetFeedId;
                $res[$public->externalId] = $a;
            }
            return $res;
        }

        public static function get_publics_info( $public_ids, $app = '' )
        {
            if( is_array( $public_ids ))
                $public_ids = implode( ',', $public_ids );
            $result = array();
            $res = VkHelper::api_request( 'groups.getById', array( 'gids' => $public_ids ), 0, $app );
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
            $params = array(
                'gid'           =>  $public_id,
                'date_from'     =>  date( 'Y-m-d', $time_from ),
                'date_to'       =>  date( 'Y-m-d', $time_to )
            );

            $res = VkHelper::api_request( 'stats.get', $params, 0 );
            $connect = ConnectionFactory::Get( 'tst' );
            if ( !empty ( $res->error)) {
                StatPublics::save_view_visitor( $public_id, null, null, null, date( 'Y-m-d', $time_from ), $connect );
                return false;
            }

            foreach( $res as $day ) {
                $subs = isset($day->reach_subscribers) ? $day->reach_subscribers : 0;
                StatPublics::save_view_visitor( $public_id, $day->views, $day->visitors, $subs, $day->day, $connect );
            }
            sleep(0.3);
            return true;
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

        public static function save_view_visitor( $public_id, $views, $visitors, $reach, $date, $connect )
        {
            $sql = 'UPDATE
                    stat_publics_50k_points
                SET
                    visitors=@visitors,
                    views   =@views,
                    reach   =@reach
                WHERE
                    id=@public_id
                    AND time=@date';

            $cmd = new SqlCommand( $sql, $connect );
            $cmd->SetInteger( '@public_id', $public_id );
            $cmd->SetInteger( '@visitors',  $visitors );
            $cmd->SetInteger( '@views',     $views );
            $cmd->SetInteger( '@reach',     $reach );
            $cmd->SetString ( '@date',      $date );
            $cmd->Execute();

        }

        //РІРѕР·РІСЂР°С‰Р°РµС‚ СЃС‚РµРЅС‹ РґРѕ 25 РїР°Р±Р»РёРєРѕРІ
        public static function get_publics_walls( $barter_events_array, $app = '' )
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
            $res = VkHelper::api_request( 'execute', array( 'code' => $code ), 0, $app );
            return $res;
        }

        public static function  get_public_walls_mk2( $walls_array, $app = '', $postponed = false )
        {
            $walls = array();
            $walls_array = array_unique( $walls_array );
            $sliced_walls_array = array_chunk( $walls_array, 25 );
            $filter = '';
            $access_token = false;

            if ( $postponed ) {
                $filter = ',"filter":"postponed"';
                $access_tokens = AccessTokenFactory::Get(array('vkId' => '187850505', 'version'=> AuthVkontakte::$Version));
                if ( !empty($access_tokens )) {
                    $access_token  = current( $access_tokens )->accessToken;
                } else {
                    return array();
                }
                $app = false;
            }
            foreach( $sliced_walls_array as $chunk ) {
                $code = '';
                $return = "return{";
                $i = 0;
                foreach( $chunk as $public ) {
                    $id = trim( $public );
                    $code   .= 'var id' . $id . ' = API.wall.get({"owner_id":-' . $id . ',"count": 6' . $filter . ' });';
                    $return .=  "\"id$id\":id$id,";
                    $i++;
                }
                $code .= trim( $return, ',' ) . "};";
                $params = array( 'code' => $code );
                if ( $access_token ) {
                    $params['access_token'] = $access_token;
                }
                $res   = VkHelper::api_request( 'execute', $params, 0, $app );
                if( isset( $res->error ))
                    continue;
                foreach( $res as $id => $content ) {
                    unset( $content[0] );
                    $walls[ str_replace( 'id', '', $id )] = $content;
                }
            }
            return $walls;
        }

        public static function get_visitors_from_vk( $public_id, $time_from, $time_to, $app = '' )
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
                $res = VkHelper::api_request( 'stats.get', $params, 0, $app );
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
            $sql = 'SELECT vk_id, name, ava, quantity, is_page
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
                    'page'      =>   $ds->GetBoolean( 'is_page')
                );
            }
            return $res;
        }

        //проверяет изменения в пабликах(название и ава)
        public static function update_public_info( $publics, $conn, $base_publics = null)
        {
            $base_publics = array_flip( $base_publics );
            $public_chunks = array_chunk( $publics, 500 );
            foreach( $public_chunks as $ids ) {
                $line = implode( ',', $ids );
                $res = VkHelper::api_request('groups.getById', array( 'gids' => $line ), 0);
                sleep(0.3);
                foreach( $res as $public ) {
                    if( !isset($public->gid) || !isset($public->photo) || !isset($public->name) || !isset($public->type))
                        continue;
                    if( !$base_publics || isset(  $base_publics[$public->gid] ) || in_array( $public->gid, WrTopics::$toface_beauty )) {
                        //проверяет, изменяется ли название паблика. если да - записывает изменения в stat_public_audit
                        $sql = 'SELECT update_public_info( @public_id, @name, @photo, @page ) AS old_name;
                                UPDATE '. TABLE_STAT_PUBLICS . ' set closed = @closed WHERE vk_id = @public_id';
                    } else {
                        $sql = 'INSERT INTO ' . TABLE_STAT_PUBLICS . '("vk_id","ava","name","is_page","sh_in_main","closed")
                                                               VALUES ( @public_id, @photo, @name, true, true, @closed)';
                    }
                    $cmd = new SqlCommand( $sql, $conn );
                    $cmd->SetInteger( '@public_id', $public->gid );
                    $cmd->SetString(  '@name',   $public->name );
                    $cmd->SetString(  '@photo',  $public->photo);
                    $cmd->SetBoolean( '@page', ( $public->type == 'page' ? true : false ));
                    $cmd->SetBoolean( '@closed', (boolean)$public->is_closed);
                    $cmd->Execute();
                }
            }
        }

        //проверяет, оменялось ли конкретное булево состояние паблика(active, in_search, closed)
        //возвращает true , если были изменения, false - нет
        //записывает изменения в
        public static function set_state( $public_id, $parameter, $state, $conn )
        {
            $sql = "SELECT set_state( @public_id, @name, @state) AS cnanged;";
                $cmd = new SqlCommand( $sql, $conn );
                $cmd->SetInteger( '@public_id', $public_id );
                $cmd->SetString ( '@name',      $parameter );
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

            $sql = 'SELECT vk_id,ava, name,quantity,is_page
                    FROM ' . TABLE_STAT_PUBLICS .
                   ' WHERE
                        ( name ILIKE @search_string
                        OR vk_id = @int_search )
                        AND active IS TRUE
                        AND quantity > 10000
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
                    'type'      =>  $ds->GetBoolean( 'page') == 't' ? 'page' : 'group',
                );
            }
            return $res;
        }

        public static function update_population( $barter_events_array, $point = 'end' )
        {
            $subscribers =  $point . '_subscribers';
            $visitors    =  $point . '_visitors';

            foreach( $barter_events_array as $barter_event ) {
                /** @var $barter_event BarterEvent */
                if ( $barter_event->status != 3 || $point == 'start' ) {
                    $time = time() + self::time_shift;

                    $res = StatPublics::get_visitors_from_vk( $barter_event->target_public, $time, $time,'barter' );
                    if( !$res ) {
                        sleep(1);
                        $time -= 44600;
                        $res = StatPublics::get_visitors_from_vk( $barter_event->target_public, $time, $time, 'barter' );
                    }
                    $barter_event->$visitors = $res['visitors'];

                    $count = 0;
                    $init_users = array();

                    $params = array(
                        'gid' => $barter_event->target_public,
                        'count' => 15,
                        'sort'  =>  'time_desc' );

                    for ( $i = 0; $i < 3; $i++ ) {
                        $res = VkHelper::api_request( 'groups.getMembers', $params, 0, 'barter');
                        if( !isset( $res->count )) {
                            sleep( 1 );
                            continue;
                        }
                        $count      =   $res->count;
                        if( $point = 'start') {
                            $init_users =  $res->users;
                        }
                        break;
                    }

                    $barter_event->$subscribers = $count;
                    if( $point == 'start' ) {
                        $barter_event->init_users  = $init_users;
                    }
                }
            }
        }

        public static function get_last_stat_demon_time()
        {
            $sql = 'SELECT MAX("createdAt") FROM ' . TABLE_STAT_PUBLICS_POINTS;

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $ds = $cmd->Execute();
            echo $cmd->GetQuery();
            $ds->Next();
            return $ds->Next() ? $ds->GetDateTime( 'max' ) : DateTimeWrapper::Now() ;
        }


        //возвращает информацию о паблике по ссылке, shrotname'у или id
        public static function getPublicInfo( $piblicId, $user_token )
        {
            $search = array( '/(.+club)(\d{1,22})$/', '/(.+public)(\d{1,22})$/', '/(.+)\/([^\/]+)$/' );

            $url = parse_url( $piblicId );
            $url = ltrim( $url['path'], '/' );
            $public_id = preg_replace( $search, '$2', $url );

            $params = array(
                'filter'        =>  'admin,editor',
                'group_id'      =>  $public_id,
                'fields'        =>  'members_count,contacts',
                'access_token'  =>  $user_token
            );
            $result = VkHelper::api_request( 'groups.getById', $params );
            if ( is_array( $result )) {
                return $result[0];
            }
            return false;
        }

        public static function isCheap( VkPublic $vkPublic, $price ) {
            return ($vkPublic->viewers_week <= 10000 && $price <= 50)  ||
                ($vkPublic->viewers_week <= 50000 && $vkPublic->viewers_week > 10000&& $price <= 200)  ||
                ($vkPublic->viewers_week <= 100000 && $vkPublic->viewers_week > 50000 && $price <= 400)  ||
                ($vkPublic->viewers_week <= 200000 && $vkPublic->viewers_week > 100000 && $price <= 800)  ||
                ($vkPublic->viewers_week <= 500000 && $vkPublic->viewers_week > 200000 && $price <= 1500)  ||
                ($vkPublic->viewers_week <= 1000000 && $vkPublic->viewers_week > 500000 && $price <= 3000)  ||
                ($vkPublic->viewers_week <= 1500000 && $vkPublic->viewers_week > 1000000 && $price <= 4500)  ||
                ($vkPublic->viewers_week <= 2000000 && $vkPublic->viewers_week > 1500000 && $price <= 6000)  ||
                ($vkPublic->viewers_week <= 3000000 && $vkPublic->viewers_week > 2000000 && $price <= 9000);
        }

        public static function checkIfCheap($vkId, $price = -1) {
            $vkPublic = VkPublicFactory::GetOne(array( 'vk_id' => $vkId));
            //если пересчитываем, внешнеуказанной цены нет
            if ( $price == -1) {
                $price = (int)$vkPublic->cpp;
            }
            if ( isset( $vkPublic->viewers_week ) && (int)$vkPublic->viewers_week ) {
                if ( $price && StatPublics::isCheap($vkPublic, $price )) {
                    $ge = new GroupEntry(
                        self::cheapGroupId,
                        $vkPublic->vk_public_id,
                        Group::STAT_GROUP,
                        AuthVkontakte::IsAuth()
                    );

                    GroupEntryFactory::Add($ge);
                } else {
                    GroupEntryFactory::DeleteByMask( array(
                        'groupId'   =>  self::cheapGroupId,
                        'entryId'   =>  $vkPublic->vk_public_id,
                        'sourceType'=>  Group::STAT_GROUP,
                    ));
                }
            }
        }
    }
?>
