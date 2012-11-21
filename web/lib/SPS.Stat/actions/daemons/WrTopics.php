<?php
//обновление данных по топу пабликов, раз в день ~6 ночи
Package::Load( 'SPS.Stat' );

set_time_limit(13600);
//error_reporting( 0 );
class WrTopics extends wrapper
{
    private $ids;
    private $conn;

    public function Execute()
    {
        $this->conn = ConnectionFactory::Get( 'tst' );
        if (! $this->check_time())
            die('Не сейчас');

        $this->get_id_arr();
        echo "start_time = " . date( 'H:i') . '<br>';
        $this->update_quantity();
        StatPublics::update_public_info( $this->ids, $this->conn );
        $this->update_visitors();
//        $this->find_admins();
        echo "end_time = " . date( 'H:i') . '<br>';
    }

    public function get_id_arr()
    {
        $sql = "select vk_id
                FROM ". TABLE_STAT_PUBLICS ."
                WHERE quantity > 50000
                ORDER BY vk_id";
        $cmd = new SqlCommand( $sql, $this->conn );
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
        $ds->Next();
        if( $ds->GetValue( 'time' ))
            return false;
        return true;
    }

    //обновление данных по каждому паблику(текущее количество, разница со вчерашним днем)
    public function set_public_grow( $publ_id, $quantity )
    {
        $sql = 'SELECT quantity FROM ' . TABLE_STAT_PUBLICS_POINTS .
            ' WHERE
                        id=@publ_id
                        AND (
                                time=CURRENT_DATE - interval \'7 day\'
                                or time=CURRENT_DATE - interval \'30 day\'
                            )
                   ORDER BY time DESC';

        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@publ_id',  $publ_id );
        $ds = $cmd->Execute();
        $time = array();
        while( $ds->Next() ) {
            $quan_arr[] = $ds->getValue('quantity', TYPE_INTEGER);
        }

        if ( isset ( $quan_arr[1] ) ) {
            $diff_rel_mon = round( ( $quantity / $quan_arr[1] - 1) * 100, 2 );
            $diff_abs_mon = $quantity - $quan_arr[1];
        } else {
            $diff_rel_mon = 0;
            $diff_abs_mon = 0;
        }

        if ( isset ( $quan_arr[0] ) ) {
            $diff_rel_week = round( ( $quantity / $quan_arr[0] - 1) * 100, 2 );
            $diff_abs_week = $quantity - $quan_arr[0];
        } else {
            $diff_rel_week = 0;
            $diff_abs_week = 0;
        }

        $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . '
            SET
                quantity=@new_quantity,
                diff_abs=(@new_quantity - quantity),
                diff_rel=round( ( @new_quantity/quantity - 1 ) * 100, 2 ),
                diff_abs_week   =   @diff_abs_week,
                diff_rel_week   =   @diff_rel_week,
                diff_abs_month  =   @diff_abs_month,
                diff_rel_month  =   @diff_rel_month
            WHERE vk_id=@publ_id';

        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@publ_id',          $publ_id );
        $cmd->SetInteger( '@diff_abs_week',    $diff_abs_week );
        $cmd->SetInteger( '@diff_abs_month',   $diff_abs_mon );
        $cmd->SetFloat( '@diff_rel_week',      $diff_rel_week );
        $cmd->SetFloat( '@diff_rel_month',     $diff_rel_mon );
        $cmd->SetFloat( '@new_quantity',       $quantity + 0.1 );
        $cmd->Execute();
    }

    //собирает количество посетителей в пабликах
    public function update_quantity()
    {
        $time = $this->morning(time());
        $i = 0;
        $return = "return{";
        $code = '';
        $timeTo = StatPublics::get_last_update_time();
        foreach( $this->ids as $b ) {

            if ( $i == 25 or !next( $this->ids )) {
                if ( !next( $this->ids ) ) {
                    $code   .= "var a$b = API.groups.getMembers({\"gid\":$b, \"count\":1});";
                    $return .= "\" a$b\":a$b,";
                }

                $code .= trim( $return, ',' ) . "};";

                if (self::TESTING)
                    echo '<br>' . $code;
                $res = VkHelper::api_request( 'execute', array('code' =>  $code), 0);

                foreach($res as $key => $entry) {
                    $key = str_replace( 'a', '', $key );
                    $sql = "INSERT INTO " . TABLE_STAT_PUBLICS_POINTS . " (id,time,quantity) values(@id,current_timestamp - interval '1 day',@quantity)";
                    $cmd = new SqlCommand( $sql, $this->conn );
                    $cmd->SetInteger( '@id',        $key );
                    $cmd->SetInteger( '@quantity',  $entry->count );
                    $cmd->Execute();
                    $this->set_public_grow( $key, $entry->count, $timeTo );
                }

                sleep(0.3);
                $i = 0;
                $return = "return{";
                $code = '';
            }
            $code   .= "var a$b = API.groups.getMembers({\"gid\":$b, \"count\":1});";
            $return .= "\" a$b\":a$b,";
            $i++;
        }
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

        $sql = 'UPDATE stat_publics_50k as a
                SET visitors=(
                    SELECT b.visitors
                    FROM stat_publics_50k_points as b
                    WHERE a.vk_id=b.id
                    ORDER BY time DESC
                    LIMIT 1)';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->Execute();
    }

    //поиск админов пабликов
    public function find_admins(  )
    {
         foreach ( $this->ids as $id ) {
            sleep(0.3);
            echo $id . '<br>';

            $params = array(
                'act'   =>  'a_get_contacts',
                'al'    =>  1,
                'oid'   =>  $id
            );

            $url = 'http://vk.com/al_page.php';
            $k = $this->qurl_request( $url, $params );
            $k = explode( '<div class="image">' ,$k );
            unset( $k[0] );
            if ( empty( $k ) )
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
        $desk = '';
        $cont = '';
        if (preg_match('/href="\/(.+?)"/', $contact_html, $matches))
            $link = $matches[1];
        if (preg_match('/<div class="extra_info.+?>(.+?)<\/div>/', $contact_html, $matches))
            $cont = $matches[1];
        if (preg_match('/<div class="desc.+?>(.+?)<\/div>/', $contact_html, $matches))
            $desc = $matches[1];
        if (preg_match('/<img src="(.+?)"/', $contact_html, $matches))
            $ava = $this->$matches[1];

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
        }
        $res = array(
            'role'  =>  TextHelper::ToUTF8( $desc . ' ' . $cont ),
            'name'  =>   $k[ 'name' ],
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
        //                $this->db_wrap('query', $query);
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