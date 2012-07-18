<?php
//обновление данных по топу пабликов, раз в день ~4.30 ночи
    Package::Load( 'SPS.Stat' );

set_time_limit(600);
    class WrTopics extends wrapper
    {
        const TESTING = false;

        public $ids;
        private $publs = array(
            'our'   =>  array(
                    'info'  =>  'publs50k',
                    'stata' =>  'gr50k'),
            'not_our'   =>  array(
                    'info'  =>  'our_publs2',
                    'stata' =>  'our_publs_points')
            );

        public function Execute()
        {
            $i = 0;


            foreach ($this->publs as $p_array) {
                $this->get_id_arr($p_array['stata']);
                $this->update_quantity($p_array['info'], $p_array['stata']);
                die();
            }
        }

        public function get_id_arr($table_name)
        {
            $sql = "select id,vk_id FROM $table_name ORDER BY id";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();

            while ( $ds->Next() ) {
                $res[$ds->getValue('id', TYPE_INTEGER)] = $ds->getValue('vk_id', TYPE_INTEGER);
            }
            $this->ids = $res;
        }

        public function get_public_grow($id, quantity)
        {



                $sql = 'UPDATE';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@publ_id',  $publ_id);
                $ds = $cmd->Execute();
                $quantity = array();

                while( $ds->Next() ) {
                    $quantity[] = $ds->getValue('quantity', TYPE_INTEGER);
                }

                $quantity_last = end($quantity);
                $quantity_comparison = prev($quantity);

                if (count($quantity) > 1  && $quantity_last != 0 && $quantity_comparison != 0 ) {
                    $diff_abs = $quantity_last - $quantity_comparison;
                    $diff_rel= round(( $quantity_last - $quantity_comparison ) / $quantity_comparison, 4) * 10000 ;
                } else {
                    $diff_abs = '-';
                    $diff_rel = '-';
                }

                $sql = 'UPDATE ' . self::T_PUBLICS_LIST . '
                SET diff_abs=quantity-@new_quantity,
                    diff_rel=ROUND((quantity-@new_quantity)/@new_quantity, 0) * 10000,
                    quantity=@new_quantity
                WHERE vk_id=@publ_id';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@publ_id',   $publ_id);
                $cmd->SetInteger('@new_quantity',  $quantity_last);
                $cmd->Execute();

        }

        public function update_quantity($info_t, $points_t)
        {
            $time = $this->morning(time());
            $i = 0;
            $return = "return{";
            $code = '';

            $id_arr = $this->get_id_arr($info_t);

            foreach($id_arr as $b) {
        //
                if ($i == 25 or !next($id_arr)) {
                    if (!next($id_arr)) {
                        $code   .= "var a$b = API.groups.getMembers({\"gid\":$b, \"count\":1});";
                        $return .= "\" a$b\":a$b,";
                    }

                    $code .= trim($return, ',') . "};";
                    $res = $this->vk_api_wrap('execute', array('code' =>  $code));

                    foreach($res as $key=>$entry) {

                        $key = str_replace('a', '', $key);

                        $sql = "insert into $points_t(id,time,quantity) values(@id,@time,@quantity)";
                        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                        $cmd->SetInteger( '@id', $key );
                        $cmd->SetInteger( '@time', $time );
                        $cmd->SetInteger( '@quantity', $entry->count );
                        $cmd->Execute();
                        echo "New entry: $key, $time, $entry->count <br>";
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