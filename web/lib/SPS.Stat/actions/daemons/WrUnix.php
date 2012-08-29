<?php
header("Content-Type: text/html; charset=utf-8");

set_time_limit(600);



class WrUnix extends wrapper
{

    const TESTING = true;
    const TEMP_TABLE = 'temp_res_uniq';

    private $ids;
    private $ununiq_id_count ;

    public function Execute()
    {
        $this->get_publics();
        $this->trunk();
        foreach($this->ids as $id) {
            $this->get_users($id[0]);
        }

        $this->find_unix();

    }

    private function get_users($id)
    {
        if (self::TESTING)
            echo $id . '<br>';

        $offset = 0;

        //собираем id юзеров паблика
        while (1) {

            $query_params = array(
                                    'gid'       =>  $id,
                                    'offser'    =>  $offset,
                                  );

            $result = $this->vk_api_wrap('groups.getMembers',$query_params);
            if (count($result->users) == 0) break;

            $values = implode('),(', $result->users);
            $values = '(' . $values . ')';

            $query = "INSERT INTO " . self::TEMP_TABLE . " (id) VALUES $values";
            $this->db_wrap('query', $query);

            $offset += 1000;
            if ($offset > 100000000) {
                throw new Exception(
                    'Something really going wrong on collecting ids from public ' . $id
                );
            }
            sleep(0.35);
        }
        $this->ununiq_id_count += $result->count;

    }

    private function trunk()
    {
        $query = 'TRUNCATE TABLE ' .  TEMP_TABLE;

    }

    public function find_unix()
    {

        $query = 'SELECT COUNT(*) FROM (SELECT DISTINCT id FROM res) AS sd';
        $this->db_wrap('query', $query);
        $result = $this->db_wrap('get_row');
        $result = end($result);
        echo '<br> unix: ';
        print_r ($result);
        return end($result);
    }
}
