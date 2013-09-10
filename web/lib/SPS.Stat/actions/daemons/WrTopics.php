<?php
//обновление данных по топу пабликов, раз в день ~6 ночи
Package::Load( 'SPS.Stat' );

set_time_limit(13600);
//error_reporting( 0 );
new stat_tables();
class WrTopics extends wrapper
{
    const TIME_LIMIT_WARNING     = 10800; //если разница больше этого времени - предупреждение о неточности
    const TIME_LIMIT_STOP_PARSE  = 43200; //если разница больше этого времени - данные по количеству подписчиков не обновляются

    private $ids;
    private $conn;

    public function Execute2()
    {
        $this->conn = ConnectionFactory::Get( 'tst' );

        set_time_limit(14000);
        if (! $this->check_time()) {
            $this->double_check_quantity();
            die('Не сейчас');
        }
        $base_publics = $this->get_id_arr( StatPublics::STAT_QUANTITY_LIMIT );
        $this->update_visitors();


        StatPublics::update_public_info( $this->ids, $this->conn, $base_publics );
//        $this->update_quantity();
        //проверка на разницу по времени
        $standard_parse_time = DateTimeWrapper::Now();
        $standard_parse_time->setTime(3,0,0);
        $date = StatPublics::get_last_stat_demon_time();
        $diff = abs( $standard_parse_time->format('U') - $date->format('U'));
        if( $diff <  self::TIME_LIMIT_STOP_PARSE ) {
            if( $diff > self::TIME_LIMIT_WARNING ) {
                $status =  StatPublics::WARNING_DATA_NOT_ACCURATE;
            } else {
                $status = StatPublics::WARNING_DATA_ACCURATE;
            }
            $this->update_quantity();
        } else {
            $status = StatPublics::WARNING_DATA_FROM_YESTERDAY;
            $this->update_quantity( null, true );
        }
        $this->create_warning( $status );
        $this->double_check_quantity();

        echo "end_time = " . date( 'H:i' ) . '<br>';
    }

    public function get_id_arr( $limit )
    {
        $sql = "select vk_id
                FROM " . TABLE_STAT_PUBLICS . "
                WHERE quantity > @limit
                AND updated_at < now() - interval '23 hour'
                ORDER BY vk_id";

        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@limit',  $limit );

        $ds = $cmd->Execute();
        $res = array();
        while ( $ds->Next() ) {
            $res[] = $ds->getInteger( 'vk_id' );
        }
        $this->ids = $res;

    }

    public function check_time()
    {
        $sql = 'SELECT time
                FROM ' . TABLE_STAT_PUBLICS_POINTS . '
                WHERE time >= current_date  - interval \'1 day\'
                LIMIT 1';
        $cmd = new SqlCommand( $sql, $this->conn );
        $ds = $cmd->Execute();
        if( $ds->Next())
            return false;
        return true;
    }

    //обновление данных по каждому паблику(текущее количество, разница со вчерашним днем)
    public function set_public_grow( $publ_id, $quantity, $name, $ava, $is_closed, $is_page, $only_quantity = null )
    {

        $show_in_main   = false;
        $diff_abs       = null;
        $diff_abs_week  = null;
        $diff_abs_month = null;
        $diff_rel       = null;
        $diff_rel_week  = null;
        $diff_rel_month = null;

        if ( $quantity > 1000 && !$only_quantity ) {
            $show_in_main = true;
            $sql = 'SELECT quantity, (now()::date -  time) as interv FROM ' . TABLE_STAT_PUBLICS_POINTS .
                  ' WHERE
                        id=@publ_id
                        AND (
                                   time=CURRENT_DATE - interval \'2 day\'
                                or time=CURRENT_DATE - interval \'8 day\'
                                or time=CURRENT_DATE - interval \'31 day\'
                            )
                    ORDER BY time DESC';

            $cmd = new SqlCommand( $sql, $this->conn );
            $cmd->SetInteger( '@publ_id',  $publ_id );
            $ds = $cmd->Execute();
            $quan_arr = array();

            while( $ds->Next() ) {
                $quan_arr[$ds->GetInteger( 'interv' )] = $ds->GetInteger('quantity');
            }

            if ( isset ( $quan_arr[31] )) {
                $diff_rel_month = $quan_arr[31] ? round(( $quantity / $quan_arr[31] - 1) * 100, 2 ) : 0;
                $diff_abs_month = $quantity - $quan_arr[31];
            }

            if ( isset ( $quan_arr[8] )) {
                $diff_rel_week = $quan_arr[8] ? round( ( $quantity / $quan_arr[8] - 1) * 100, 2 ) : 0;
                $diff_abs_week = $quantity - $quan_arr[8];

            }

            if ( isset ( $quan_arr[2] ) && $quan_arr[2] ) {
                $diff_rel = $quan_arr[2] ? round( ( $quantity / $quan_arr[2] - 1) * 100, 2 ) : 0;
                $diff_abs = $quantity - $quan_arr[2];
            } else {
                $show_in_main = false;
            }
        }

        if ( !$only_quantity ) {
        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . '
                SET
                    quantity        =   @new_quantity,
                    diff_abs        =   @diff_abs,
                    diff_rel        =   @diff_rel,
                    diff_abs_week   =   @diff_abs_week,
                    diff_rel_week   =   @diff_rel_week,
                    diff_abs_month  =   @diff_abs_month,
                    diff_rel_month  =   @diff_rel_month,
                    sh_in_main      =   @sh_in_main,
                    name            =   @name,
                    closed          =   @closed,
                    ava             =   @ava,
                    updated_at      =   now(),
                    active          =   true,
                    is_page         =   @is_page
                 WHERE
                    vk_id = @publ_id;
                ';
        }  else {
            $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . '
                SET
                    quantity = @new_quantity,
                    updated_at      =   now()
                WHERE
                    vk_id = @publ_id;
                ';
        }

        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@publ_id',          $publ_id );
        $cmd->SetInteger( '@diff_abs_week',    $diff_abs_week );
        $cmd->SetInteger( '@diff_abs_month',   $diff_abs_month );
        $cmd->SetFloat( '@diff_rel_week',      $diff_rel_week );
        $cmd->SetFloat( '@diff_rel_month',     $diff_rel_month );
        $cmd->SetFloat( '@new_quantity',       $quantity + 0.1 );
        $cmd->SetFloat( '@diff_rel',           $diff_rel );
        $cmd->SetFloat( '@diff_abs',           $diff_abs );
        $cmd->SetBoolean( '@sh_in_main',       $show_in_main );
        $cmd->SetString( '@ava',               $ava );
        $cmd->SetString( '@name',              $name );
        $cmd->SetBoolean('@closed',            $is_closed);
        $cmd->SetBoolean('@is_page',            $is_page);
        $cmd->Execute();

    }

    public function set_public_closed( $public_id )
    {
        $sql = "UPDATE " . TABLE_STAT_PUBLICS . " SET closed = 't' WHERE vk_id=@id ";
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id', $public_id );
        $cmd->Execute();
    }

    //собирает количество посетителей в пабликах
    //ids - если нужно проверить какие-то конкретные паблики
    //если $only_quantity, не обновляем данные по росту за этот день(только общее количество подписчиков)
    public function update_quantity( $ids = null, $only_quantity = null )
    {
        $act = 'update';
        if( !is_array( $ids )) {
            $ids = $this->ids;
            $act = 'new';
        }
        if( empty( $ids ))
            return true;
        $ids_chunks_array = array_chunk( $ids, 25 );

        foreach ( $ids_chunks_array as $ids_chunk ) {
            $return = "return{";
            $code = '';
            foreach( $ids_chunk as $id ) {
                if ( $act == 'new' ) $this->save_point( $id, 0 );
                $code   .= "var a$id = API.groups.getMembers({\"gid\":$id, \"count\":1});";
                $return .= "\" a$id\":a$id,";
            }
            $code .= trim( $return, ',' ) . "};";
            for( $i = 0; $i < 3; $i++) {
                $res = VkHelper::api_request( 'execute', array('code' =>  $code ), 0);

                if ( empty( $res ) || isset( $res->error)) {
                    sleep(0.3);
                    continue;
                }
                foreach( $res as $key => $entry ) {
                    if ( $entry ) {
                        $count = isset( $entry->count ) ? $entry->count : 0;
                        $key = str_replace( 'a', '', $key );
                        $this->update_point( $key, $count );

                        if ( !$count )
                            continue;
//                        $this->set_public_grow( $key, $count, $only_quantity );
                    } else {
                        $this->set_public_closed( $key );
                    }
                }
                break;
            }
        }
    }

    public function save_point( $public_id, $quantity )
    {
        $sql = "INSERT INTO " . TABLE_STAT_PUBLICS_POINTS . " (id,time,quantity) values(@id,current_timestamp - interval '1 day',@quantity)";
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id',        $public_id );
        $cmd->SetInteger( '@quantity',  $quantity ? $quantity : 0 );
        $cmd->Execute();
    }

    public function update_point( $public_id, $quantity )
    {
        $sql = "UPDATE " . TABLE_STAT_PUBLICS_POINTS .
              " SET quantity = @quantity,
                    \"createdAt\" = now()
                WHERE id=@id
                      AND time = (now() - interval '1 day')::date
        ";
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id',        $public_id );
        $cmd->SetInteger( '@quantity',  $quantity ? $quantity : -1 );
        $cmd->Execute();
    }

    public function getFaultedPublics()
    {
        $sql = 'SELECT id FROM '
            . TABLE_STAT_PUBLICS_POINTS . '
           WHERE time > now() - interval \'2 day\'
           AND   ( quantity is null or quantity = 0 )
           AND   id > 0';
        $cmd = new SqlCommand( $sql, $this->conn );
        echo $cmd->GetQuery();
        $ds = $cmd->Execute();
        $res = array();
        while( $ds->Next() ) {
            $res[] = $ds->GetInteger( 'id' );
        }
        return $res;
    }

    public function get_all_visitors()
    {
        $time_start = time() - 86400 * 30;
        $time_stop  = time() - 75600;
        foreach( $this->ids as $public_id ) {
            StatPublics::get_views_visitors_from_vk( $public_id, $time_start, $time_stop );
        }
    }

    public function update_visitors( $ids = false )
    {
        if( !$ids ) {
            $ids = $this->ids;
        }
        $time_start = time() - 75600 ;
        foreach( $ids as $public_id ) {
            StatPublics::get_views_visitors_from_vk( $public_id, $time_start, $time_start );
        }

        $sql = 'UPDATE stat_publics_50k as publics
                SET visitors_month=points.visitors_sum, viewers_month=points.viewers_sum
                FROM
                        ( SELECT  avg(visitors) as visitors_sum, avg(reach) as viewers_sum,id FROM stat_publics_50k_points
                          WHERE time > now()-interval \'1 month\' AND time < now() group by id )
                                as points
                WHERE publics.vk_id=points.id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
        $sql = 'UPDATE stat_publics_50k as publics
                SET visitors_week=points.visitors_sum, viewers_week=points.viewers_sum
                FROM
                        ( SELECT  avg(visitors) as visitors_sum, avg(reach) as viewers_sum,id FROM stat_publics_50k_points
                          WHERE time > now()-interval \'1 week\' AND time < now() group by id )
                                as points
                WHERE publics.vk_id=points.id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
        $sql = 'UPDATE stat_publics_50k as publics
                SET visitors=points.visitors_sum, viewers=points.viewers_sum
                FROM
                        ( SELECT  sum(visitors) as visitors_sum, sum(reach) as viewers_sum,id FROM stat_publics_50k_points
                          WHERE time > now()-interval \'2 day\' AND time < now() group by id )
                                as points
                WHERE publics.vk_id=points.id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    //поиск админов пабликов
    public function find_admins( )
    {
        foreach ( $this->ids as $id ) {
            if ( $id < 0 )
                continue;
            sleep(0.3);
            $params = array(
                'act'   =>  'a_get_contacts',
                'al'    =>  1,
                'oid'   =>  '-' . $id
            );

            $url = 'http://vk.com/al_page.php';
            $k = $this->qurl_request( $url, $params );
            $k = explode( '<div class="image">', $k );
            unset( $k[0] );

            if ( empty( $k ))
                continue;
            $this->delete_admins( $id );
            foreach( $k as $admin_html ) {
                $admin = $this->get_admin('a href="/' . $admin_html);
                if ( !empty( $admin )) {
                    $this->save_admin( $id, $admin );
                }
                $admin = array();
            }
        }
        return true;
    }

    private function get_admin( $contact_html )
    {
        $search_array = array( '<span class="info_email">', '<span class="info_phone">', '</span>', '</a>' );
        $desc = '';
        $cont = '';
        $link = '';
        if ( preg_match('/href="\/(.+?)"/', $contact_html, $matches))
            $link = $matches[1];
        if ( preg_match('/<div class="extra_info.+?>(.+?)<\/div>/', $contact_html, $matches)) {
            $cont = $matches[1];
//            $cont = str_replace( '', '', $cont );
            $cont = strip_tags( $cont );
        }
        if ( preg_match( '/<div class="desc.+?>(.+?)<\/div>/', $contact_html, $matches )) {
            $desc = $matches[1];
//            $desc = str_replace( '', '', $desc );
            $desc = strip_tags( $desc );
        }
        if ( preg_match('/<img src="(.+?)"/', $contact_html, $matches))
            $ava  = $matches[1];

        if ( isset( $ava ) && substr_count( $ava, 'deactivated' ))
            return false;

        if( !$link && !$desc && !$cont ){
            return false;
        }
        $k = array();
        if ( $link ) {
            $link = trim( $link, '/' );
            $k = StatUsers::get_vk_user_info( $link );

            $k = reset( $k );

        } else {
            return array();
        }
        $res = array(
            'role'  =>  TextHelper::ToUTF8( $desc . ' ' . $cont ),
            'name'  =>  $k[ 'name' ],
            'vk_id' =>  $k['userId'],
            'ava'   =>  isset( $ava )? $ava : $k['ava']
        );
        return $res;
    }

    private function save_admin( $public_id, $admin_data )
    {
        $sql = "INSERT INTO " . TABLE_STAT_ADMINS . "
                                   (
                                    vk_id,
                                    role,
                                    name,
                                    ava,
                                    publ_id
                                    )
                            VALUES (
                                    @vk_id,
                                    @role,
                                    @name,
                                    @ava,
                                    @public_id
                                  )";
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger('@vk_id', $admin_data['vk_id']);
        $cmd->SetInteger('@public_id', $public_id );
        $cmd->SetString( '@role',  $admin_data['role']);
        $cmd->SetString( '@name',  $admin_data['name']);
        $cmd->SetString( '@ava',   $admin_data['ava']);
        $cmd->Execute();
    }

    private function delete_admins( $public_id )
    {
        $sql = 'DELETE FROM ' . TABLE_STAT_ADMINS . '
                WHERE publ_id = @public_id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger('@public_id', $public_id );
        $cmd->Execute();
    }

    private function create_warning( $status ) {
        $sql = 'UPDATE serv_states SET status_id = @status WHERE serv_name = \'stat\'';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@status', $status );
        $cmd->Execute();
    }

    public function Execute()
    {
        $this->conn = ConnectionFactory::Get( 'tst' );

        set_time_limit(14000);
        if ( !$this->check_time()) {
            $ids = $this->getFaultedPublics();

            if ( empty( $ids )) {
                die('Не сейчас');
            } else {
                $this->updateCommonInfo($ids);
                $this->update_visitors($ids);
            }
            die();
        }

        $start = microtime(1);
        $this->get_id_arr( 1000000 );
        $this->createPoints();
        $this->updateCommonInfo();
        $this->update_visitors();
        echo '<br>' . round( microtime(1) - $start);
    }

    public function createPoints() {
        $date = date( 'Y-m-d', time() - 86400) ;
        $sql = 'SELECT * FROM stat_publics_50k_points
                WHERE time=@time';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetString ( '@time',      $date );
        $ds = $cmd->Execute();
        if ( $ds->GetSize() == count($this->ids) ) {
            print_r( count( $this->ids ));
            echo 'fuck';
            die();
            return;
        }
        $sql = 'INSERT INTO
                    stat_publics_50k_points
                VALUES( @public_id, @date, 0, 0, 0, 0, now())';
        $cmd = new SqlCommand( $sql, $this->conn );

        foreach( $this->ids as $id) {
            $cmd->ClearParameters();
            $cmd->SetInteger( '@public_id', $id );
            $cmd->SetString ( '@date',      $date );
            $cmd->Execute();
        }
    }

    public function updatePoint( $publicId, $quantity, $reach, $visitors, $views, $reach )
    {
        $time = date( 'Y-m-d', time() - 86400) ;
        $sql = 'UPDATE stat_publics_50k_points
                SET quantity=@quantity,
                    visitors=@visitors,
                    reach=@reach,
                    views=@views
                WHERE time=@time AND id=@id ';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $cmd->SetInt( '@quantity', $quantity);
        $cmd->SetInt( '@visitors', $visitors);
        $cmd->SetInt( '@reach',    $reach);
        $cmd->SetInt( '@views',    $views);
        $cmd->SetInt( '@id',       $publicId);
        $cmd->SetString('@time',   $time);
        $cmd->Execute();
    }

    public function updateCommonInfo( $publicIds = false ) {
        if (!$publicIds) {
            $publicIds = $this->ids;
        }
        $publcIdsChunks = array_chunk( $publicIds, 200 );
        $start = microtime(1);
        foreach ( $publcIdsChunks as $chunk ) {
            $idsRow = implode(',', $chunk);
            $params = array(
                'group_ids' =>  $idsRow,
                'fields'    =>  'members_count',
            );
            try {
                $res = VkHelper::api_request('groups.getById', $params );
            } catch ( Exception $ex ) {
                $this->clearPublicRecords($idsRow);
                print_r( $ex );
                continue;
            }

            $res = ArrayHelper::Collapse( $res, 'gid', $convertToArray = false );

            foreach( $chunk as $publicId ) {
                if ( isset( $res[$publicId] )) {
                    if ( isset($res[$publicId]->deactivated ) ) {
                        $public = new VkPublic();
                        $public->active = false;
                        $public->updated_at = DateTimeWrapper::Now();
                        VkPublicFactory::UpdateByMask( $public, array( 'active,updated_at' ), array( 'vk_id' => $publicId ));
                      echo 'vk.com/club' . $publicId . ' take ban<br>';
                        continue;
                    }
                    $this->updatePoint( $publicId, $res[$publicId]->members_count, 0, 0, 0, 0 );

                    $this->set_public_grow(
                        $publicId,
                        isset( $res[$publicId]->members_count ) ? $res[$publicId]->members_count : 0,
                        $res[$publicId]->name,
                        $res[$publicId]->photo,
                        isset( $res[$publicId]->is_closed ) ? $res[$publicId]->is_closed : 0,
                        $res[$publicId]->type == 'page'
                    );
                }

            }

        }
    }

    //все числовые значения(кроме количества) приводятся в null - для крестика
    public function clearPublicRecords( $idsRow )
    {
        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET
                    diff_abs         = NULL,
                    diff_rel         = NULL,
                    diff_abs_week    = NULL,
                    diff_rel_week    = NULL,
                    diff_abs_month   = NULL,
                    diff_rel_month   = NULL
                WHERE vk_id IN (' . $idsRow . ')';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }
}
?>
