<?php
//обновление данных по топу пабликов, раз в день ~4.30 ночи
    Package::Load( 'SPS.Stat' );

set_time_limit(600);
    class WrTopics extends wrapper
    {
        const TESTING = false;
        const BASE_RENEW = true;
        public $id; // id паблика
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
                echo $i++ . '<br>';
                $this->update_quantity($p_array['info'], $p_array['stata']);
            }
        }

        public function get_id_arr($table_name)
        {
            $sql = "select id,vk_id FROM $table_name ORDER BY id";
            $this->db_wrap('query', $sql);
            $res = array();
            while ($row = $this->db_wrap('get_row')) {
                $res[$row['id']] = $row['vk_id'];
            }
            return $res;
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

//                    echo '----------------<br>';
                    $code .= trim($return, ',') . "};";

//                    echo '<br>';
//                    echo $code;
                    $res = $this->vk_api_wrap('execute', array('code' =>  $code));

                    foreach($res as $key=>$entry) {
                        $key = str_replace('a', '', $key);
//                        print_r (array($key, $entry->count));
                        $values = '(' . $key . ','. $time . ','. $entry->count . ')';
                        $sql = "insert into $points_t(id,time,quantity) values" . $values;
                        $this->db_wrap('query', $sql);
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

//            $values = trim($values, ',');
//            $sql = "insert into $points_t(id,time,quantity) values" . $values;
//            $this->db_wrap('query', $sql);
//            echo $sql;
        }
}



?>