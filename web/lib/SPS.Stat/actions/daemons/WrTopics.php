<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );
    Package::Load( 'SPS.Stat' );
//    include 'wrapper.php';

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
        $this->db_wrap('connect');
        $i = 0;
        foreach ($this->publs as $p_array) {
            echo $i++ . '<br>';
            $this->update_quantity($p_array['info'], $p_array['stata']);
      }


    }
        public function update_quantity($info_t, $points_t)
        {
            $time = $this->morning(time());
            $i = 0;
            $return = "return{";
            $code = '';
//            $this->db->select_db('muspel');
            $sql = "select id,vk_id FROM $info_t ORDER BY id";
            $res = $this->db_wrap('query', $sql);
            $id_arr = array();
            while ($row = $this->db_wrap('get_row')) {
                $id_arr[$row['id']] = $row['vk_id'];
            }
            $values = '';
//            $sql_t = 'SELECT * FROM publs50k LIMIT 1';
            foreach($id_arr as $b) {
        //
                if ($i == 25 or !next($id_arr)) {
//                    $this->db_wrap('query', $sql_t);
                    if (!next($id_arr)) {
                        $code   .= "var a$b = API.groups.getMembers({\"gid\":$b, \"count\":1});";
                        $return .= "\" a$b\":a$b,";
                    }

                    echo '----------------<br>';
                    $code .= trim($return, ',') . "};";

                    echo '<br>';
                    echo $code;
                    $res = $this->vk_api_wrap('execute', array('code' =>  $code));

                    foreach($res as $key=>$entry) {
                        $key = str_replace('a', '', $key);
                        print_r (array($key, $entry->count));
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
            echo $sql;
        }
}



?>