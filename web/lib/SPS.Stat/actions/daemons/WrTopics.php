<?php
//обновление данных по топу пабликов, раз в день ~6 ночи
Package::Load( 'SPS.Stat' );

set_time_limit(13600);
//error_reporting( 0 );
new stat_tables();
class WrTopics extends wrapper
{
    const STAT_QUANTITY_LIMIT = 30000;

    private $ids;
    private $conn;
    public static $toface_beauty = array(
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

    public function Execute()
    {
        $this->conn = ConnectionFactory::Get( 'tst' );


        set_time_limit(14000);
        if (! $this->check_time()) {
            $this->double_check_quantity();
            die('Не сейчас');
        }
        $base_publics = $this->get_id_arr( self::STAT_QUANTITY_LIMIT );
        $this->update_visitors();
        StatPublics::update_public_info( $this->ids, $this->conn, $base_publics );
        $this->update_quantity();
        $this->double_check_quantity();

        echo "end_time = " . date( 'H:i' ) . '<br>';
    }

    public function get_id_arr( $limit )
    {
        $sql = "select vk_id
                FROM " . TABLE_STAT_PUBLICS . "
                WHERE quantity > @limit
                ORDER BY vk_id";
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@limit',  $limit );
        $ds = $cmd->Execute();
        $res = array();
        while ( $ds->Next() ) {
            $res[] = $ds->getInteger( 'vk_id' );
        }


        $this->ids =  array_merge( $res, self::$toface_beauty );
        return $res;

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
    public function set_public_grow( $publ_id, $quantity )
    {
//        echo 'паблик ' . $publ_id . '<br>';
        if ( $quantity < 1000 ) {
            return false;
        }
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
            $diff_rel_mon = $quan_arr[31] ? round(( $quantity / $quan_arr[31] - 1) * 100, 2 ) : 0;
            $diff_abs_mon = $quantity - $quan_arr[31];
        } else {
            $diff_rel_mon = 0;
            $diff_abs_mon = 0;
        }

        if ( isset ( $quan_arr[8] )) {
            $diff_rel_week = $quan_arr[8] ? round( ( $quantity / $quan_arr[8] - 1) * 100, 2 ) : 0;
            $diff_abs_week = $quantity - $quan_arr[8];
        } else {
            $diff_rel_week = 0;
            $diff_abs_week = 0;
        }

        if ( isset ( $quan_arr[2] )) {
            $diff_rel = $quan_arr[2] ? round( ( $quantity / $quan_arr[2] - 1) * 100, 2 ) : 0;
            $diff_abs = $quantity - $quan_arr[2];
        } else {
            $diff_rel = 0;
            $diff_abs = 0;
        }

        if( !isset( $quan_arr[2])) {
//            echo 'отсеял по разнице '; print_r( array( '$diff_abs' => $diff_abs, '$diff_rel' => $diff_rel ) ); echo '<br>' . $cmd->GetQuery() . '<br>' ;
            return false;
        }
        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . '
            SET
                quantity        =   @new_quantity,
                diff_abs        =   @diff_abs,
                diff_rel        =   @diff_rel,
                diff_abs_week   =   @diff_abs_week,
                diff_rel_week   =   @diff_rel_week,
                diff_abs_month  =   @diff_abs_month,
                diff_rel_month  =   @diff_rel_month
            WHERE
                vk_id = @publ_id';

        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@publ_id',          $publ_id );
        $cmd->SetInteger( '@diff_abs_week',    $diff_abs_week );
        $cmd->SetInteger( '@diff_abs_month',   $diff_abs_mon );
        $cmd->SetFloat( '@diff_rel_week',      $diff_rel_week );
        $cmd->SetFloat( '@diff_rel_month',     $diff_rel_mon );
        $cmd->SetFloat( '@new_quantity',       $quantity + 0.1 );
        $cmd->SetFloat( '@diff_rel',           $diff_rel );
        $cmd->SetFloat( '@diff_abs',           $diff_abs );
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
    public function  update_quantity( $ids = null )
    {
        $act = 'update';
        if( !is_array( $ids )) {
            $ids = $this->ids;
            $act = 'new';
        }
        if( empty( $ids ))
            return true;
        $ids_chunks_array = array_chunk( $ids, 25 );

        $timeTo = StatPublics::get_last_update_time();
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
                        $this->set_public_grow( $key, $count, $timeTo );
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
        $sql = "UPDATE " . TABLE_STAT_PUBLICS_POINTS . " SET quantity = @quantity WHERE id=@id AND time = (now() - interval '1 day')::date";
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@id',        $public_id );
        $cmd->SetInteger( '@quantity',  $quantity ? $quantity : -1 );
        $cmd->Execute();
    }

    public function double_check_quantity()
    {
        $sql = 'SELECT id FROM '
            . TABLE_STAT_PUBLICS_POINTS . '
           WHERE time > now() - interval \'2 day\'
           AND   ( quantity is null or quantity < 1)
           AND   id > 0';
        $cmd = new SqlCommand( $sql, $this->conn );
        $ds = $cmd->Execute();
        $res = array();
        while( $ds->Next() ) {
            $res[] = $ds->GetInteger( 'id' );
        }
        $this->update_quantity( $res );
    }

    public function get_all_visitors()
    {
        $time_start = time() - 86400 * 30;
        $time_stop  = time() - 75600;
        foreach( $this->ids as $public_id ) {
            StatPublics::get_views_visitors_from_vk( $public_id, $time_start, $time_stop );
        }
    }

    public function update_visitors()
    {
        $time_start = time() - 75600 ;
        foreach( $this->ids as $public_id ) {
            StatPublics::get_views_visitors_from_vk( $public_id, $time_start, $time_start );
        }

        $sql = 'UPDATE stat_publics_50k as publics
                SET visitors_month=points.visitors_sum, viewers_month=points.viewers_sum
                FROM
                        ( SELECT  sum(visitors) as visitors_sum, sum(reach) as viewers_sum,id FROM stat_publics_50k_points
                          WHERE time > now()-interval \'1 month\' AND time < now() group by id )
                                as points
                WHERE publics.vk_id=points.id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
        $sql = 'UPDATE stat_publics_50k as publics
                SET visitors_week=points.visitors_sum, viewers_week=points.viewers_sum
                FROM
                        ( SELECT  sum(visitors) as visitors_sum, sum(reach) as viewers_sum,id FROM stat_publics_50k_points
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
            $k = explode( '<div class="image">' ,$k );
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
}
?>