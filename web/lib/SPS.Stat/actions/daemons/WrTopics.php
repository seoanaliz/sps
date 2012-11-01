<?php
//обновление данных по топу пабликов, раз в день ~6 ночи
Package::Load( 'SPS.Stat' );

set_time_limit(13600);
error_reporting( 0 );
class WrTopics extends wrapper
{
    private $ids;

    public function Execute()
    {
//        if (! $this->check_time())
//            die('Не сейчас');
        $this->get_id_arr();
        echo "start_time = " . date( 'H:i') . '<br>';
        $this->update_quantity();
        $this->update_public_info();
        $this->update_visitors();
        echo "end_time = " . date( 'H:i') . '<br>';

    }

    public function get_id_arr()
    {
        $sql = "select vk_id
                FROM ". TABLE_STAT_PUBLICS ."
                WHERE quanity > 500000
                ORDER BY vk_id";
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );

        $ds = $cmd->Execute();
        $res = array();
        while ( $ds->Next() ) {
            $res[] = $ds->getValue('vk_id', TYPE_INTEGER);
        }
        $this->ids = $res;
    }

    public function check_time()
    {
        $sql = 'SELECT time
                FROM ' . TABLE_STAT_PUBLICS_POINTS . '
                WHERE time >= current_date-interval \'1 day\'
                LIMIT 1';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $ds = $cmd->Execute();
        $ds->Next();
        if( $ds->GetValue( 'time' ))
            return false;

    }

    //проверяет изменения в пабликах(название и ава)
    public function update_public_info()
    {
        if (self::TESTING)
            echo '<br>update_public_info<br>';
        $i = 0;
        $ids = '';
        $count = count($this->ids);
        foreach($this->ids as $id) {
            if ($i == 450 || $i == $count - 1)
            {
                $params  = array(
                    'gids'  =>  $ids
                );

                $res = VkHelper::api_request('groups.getById', $params, 0);
                foreach($res as $public) {
                    $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET
                                                name=@name,
                                                ava=@photo
                                WHERE
                                                vk_id=@vk_id';
                    $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                    $cmd->SetInteger('@vk_id', $public->gid);
                    $cmd->SetString('@name', $public->name );
                    $cmd->SetString('@photo', $public->photo);
                    $cmd->Execute();
                }

                $count -= 450;
                $ids = '';
                $i = 0;

            }
            $i ++;
            $ids .=  $id . ',';
        }
    }

    //обновление данных по каждому паблику(текущее количество, разница со вчерашним днем)
    public function set_public_grow( $publ_id, $quantity, $last_up_time )
    {
        $sql = 'SELECT quantity FROM ' . TABLE_STAT_PUBLICS_POINTS .
            ' WHERE
                        id=@publ_id
                        AND (
                                time=CURRENT_DATE - interval \'3 day\'
                                or time=CURRENT_DATE - interval \'7 day\'
                            )
                   ORDER BY time DESC';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );

        $cmd->SetInteger( '@time',     $last_up_time );
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

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
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
        $conn = ConnectionFactory::Get( 'tst' );
        foreach( $this->ids as $b ) {

            if ( $i == 25 or !next( $this->ids ) ) {
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
                    $cmd = new SqlCommand( $sql, $conn );
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
        $time_start = time() - 75600 ;
        $time_stop  = time() - 86400 * 30;
        foreach( $this->ids as $public_id ) {
            StatPublics::get_views_visitors_from_vk( $public_id, $time_start, $time_stop );
            die();
        }
    }
    public function update_visitors()
    {
        $time_start = time() - 75600 ;
        foreach( $this->ids as $public_id ) {
            StatPublics::get_views_visitors_from_vk( $public_id, $time_start, $time_start );
        }
    }

//возвращает данные о наших пабликах
    private function get_our_publics_state()
    {
        $publics    =   StatPublics::get_our_publics_list();
        foreach( $publics as &$public ) {
//            $authors_posts      = StatPublics::get_ad_public_posts( 10, $time_start, $time_stop );
            $authors_posts      = StatPublics::get_public_posts( $public['sb_id'], 1, $time_start, $time_stop );
            $non_authors_posts  = StatPublics::get_public_posts( $public['sb_id'], 0, $time_start, $time_stop  );

            $posts_quantity = $authors_posts['count'] + $non_authors_posts['count'];

            //всего постов
            $public['overall_posts'] = $posts_quantity;
            $days = round( ( $time_stop - $time_start ) / 84600 );
            $public['posts_days_rel'] = round( $posts_quantity / $days );
            print_r($public);

            //постов из источников
            $public['sb_posts_count'] = $non_authors_posts['count'];
            // средний rate спарсенных постов
            $public['sb_posts_rate'] = StatPublics::get_average_rate( $public['sb_id'], $time_start, $time_stop );
            //todo главноредакторских постов непосредственно на стену, гемор!!!!! <- в демона

            //процент авторских постов
            $guests = StatPublics::get_views_visitors_from_base( $public['sb_id'], $time_start, $time_stop );
            if ( !$guests ){
                $guests = StatPublics::get_views_visitors_from_vk( $public['id'], $time_start, $time_stop );
            }
            if ( $guests ) {
                $public['views'] = $guests['views'];
                $public['visitors'] = $guests['visitors'];
                $public['avg_vie_grouth'] = $guests['vievs_grouth'];
                $public['avg_vis_grouth'] = $guests['vis_grouth'];

            }

            if ( !$authors_posts['count'] && !$non_authors_posts['count'] ) {
                $public['auth_posts'] = 'какой-то косяк, данных нет';
                $public['auth_reposts_eff'] = 'данных нет';
                $$public['auth_likes_eff'] = 'данных нет';
            } elseif( !$authors_posts['count'] ) {
                $public['auth_posts'] = "авторских постов нет (всего постов - $posts_quantity)";
                $public['auth_likes_eff'] = 'с лайками та же история, средний неавторский по паблику - ' . $non_authors_posts['likes'];
                $public['auth_reposts_eff'] = 'репосты туда же, среднее - ' . $non_authors_posts['reposts']
                    . ', среднее относительное - ' . ( round( 100 * $non_authors_posts['reposts'] / $non_authors_posts['likes'], 1 ) . '%');
            }
            elseif( !$non_authors_posts['count'] ) {
                $public['auth_posts'] = "неавторских постов нет (всего постов - $posts_quantity)";

                $public['auth_likes_eff'] = 'с лайками та же история, средний по паблику - ' . $authors_posts['likes'];
                $public['auth_reposts_eff'] = 'репосты туда же, среднее - ' . $authors_posts['reposts']
                    . ', среднее относительное - ' . ( round( 100 * $authors_posts['reposts'] / $authors_posts['likes'], 1 ) . '%');
            } else {
                $public['auth_posts'] = ( $authors_posts['count'] / $posts_quantity ) * 100;
                $public['auth_posts'] = round( $public['auth_posts'], 2 ) . '%';
                $public['auth_likes_eff']   = (round( $authors_posts['likes']   / $non_authors_posts['likes'], 4 ) * 100) . '%';
                $public['auth_reposts_eff'] = (round( $authors_posts['reposts'] / $non_authors_posts['reposts'], 4 ) * 100 ) . '%';
            }
        }
        $this->show_publics( $publics );
    }

}

?>