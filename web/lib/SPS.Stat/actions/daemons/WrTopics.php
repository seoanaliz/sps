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
        public function set_public_grow($publ_id, $quantity, $period=1)
        {
                $sql = 'UPDATE ' . TABLE_STAT_PUBLICS . '
                SET diff_abs=(@new_quantity - quantity),
                    quantity=@new_quantity,
                    diff_rel=round((@new_quantity/quantity - 1)*100, 2)
                WHERE vk_id=@publ_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@publ_id',   $publ_id);
                $cmd->SetFloat('@new_quantity',  $quantity + 0.1);
                $cmd->Execute();
        }

        //собирает количество поситителей в пабликах
        public function update_quantity()
        {
            $time = $this->morning(time());
            $i = 0;
            $return = "return{";
            $code = '';

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

                        $this->set_public_grow($key, $entry->count);

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