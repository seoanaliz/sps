<?php
//обновление данных по топу пабликов, раз в день ~6 ночи
    Package::Load( 'SPS.Stat' );

    set_time_limit(600);
    class WrTopics extends wrapper
    {
        private $ids;

        public function Execute()
        {
            if (! $this->check_time())
                die('Не сейчас');

            $this->get_id_arr();

            $this->update_quantity();
            $this->update_public_info();
        }

        public function get_id_arr()
        {
            $sql = "select vk_id FROM ". TABLE_STAT_PUBLICS ." ORDER BY vk_id";
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
            $sql = 'SELECT MAX(time) FROM ' . TABLE_STAT_PUBLICS_POINTS . ' LIMIT 1';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();
            $ds->Next();
            $diff =  time() - $ds->getValue('max', TYPE_INTEGER);
            if (self::TESTING)
                echo '<br>differing = ' . $diff . '<br>';
            if ($diff < 86400 )
                return false;
            if ($diff > 86400 * 2 )
                return ($diff / 86400);
            return 1;

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

                    $res = $this->vk_api_wrap('groups.getById', $params);
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
                                time = @time - 86400 * 7
                            OR  time = @time - 86400 * 30
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
            echo '<br>' . $sql;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ) );
            $cmd->SetInteger( '@publ_id',          $publ_id );
            $cmd->SetInteger( '@diff_abs_week',    $diff_abs_week );
            $cmd->SetInteger( '@diff_abs_month',   $diff_abs_mon );
            $cmd->SetFloat( '@diff_rel_week',      $diff_rel_week );
            $cmd->SetFloat( '@diff_rel_month',     $diff_rel_mon );
            $cmd->SetFloat( '@new_quantity',       $quantity + 0.1 );
            $cmd->Execute();

        }

        //собирает количество поситителей в пабликах
        public function update_quantity()
        {
            $time = $this->morning(time());
            $i = 0;
            $return = "return{";
            $code = '';
            $time = StatPublics::get_last_update_time();
            foreach($this->ids as $b) {

                if ($i == 25 or !next($this->ids)) {
                    if (!next($this->ids)) {
                        $code   .= "var a$b = API.groups.getMembers({\"gid\":$b, \"count\":1});";
                        $return .= "\" a$b\":a$b,";
                    }

                    $code .= trim($return, ',') . "};";

                    if (self::TESTING)
                        echo '<br>' . $code;
                    $res = $this->vk_api_wrap('execute', array('code' =>  $code));

                    foreach($res as $key => $entry) {

                        $key = str_replace('a', '', $key);
                        $sql = "INSERT INTO " . TABLE_STAT_PUBLICS_POINTS . " (id,time,quantity) values(@id,@time,@quantity)";
                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                        $cmd->SetInteger( '@id',        $key );
                        $cmd->SetInteger( '@time',      $time );
                        $cmd->SetInteger( '@quantity',  $entry->count );
                        $cmd->Execute();

                        $this->set_public_grow( $key, $entry->count, $time );


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
}

?>