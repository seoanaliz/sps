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
        const T_PUBLICS_POINTS = 'gr50k';
        const T_PUBLICS_LIST   = 'publs50k';


        public function Execute()
        {
            $i = 0;


//            die();
            foreach ($this->publs as $p_array) {
                $this->get_id_arr($p_array['stata']);
                $this->get_public_grow();
//                $this->update_quantity($p_array['info'], $p_array['stata']);
                die();
            }
        }

        public function get_id_arr()
        {
            $sql = "select vk_id FROM " . self::T_PUBLICS_LIST. " ORDER BY vk_id";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $ds = $cmd->Execute();

            while ( $ds->Next() ) {
                $res[$ds->getValue('vk_id', TYPE_INTEGER)] = $ds->getValue('vk_id', TYPE_INTEGER);
            }
            $this->ids = $res;
        }

        public function get_public_grow()
        {
            foreach($this->ids as $id) {

                $sql = 'SELECT quantity FROM ' . self::T_PUBLICS_POINTS . ' WHERE id=@publ_id ORDER BY "time" DESC LIMIT 1';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@publ_id',  $id);
                $ds = $cmd->Execute();
                $ds->Next();
                $quantity = $ds->getValue('quantity', TYPE_INTEGER);
                echo $id.' '.$quantity . '<br>';
                $sql = 'UPDATE ' . self::T_PUBLICS_LIST . '
                        SET diff_abs=@new_quantity - quantity,
                            diff_rel=ROUND(@new_quantity * 1000/ quantity - 1000, 0),
                            quantity=@new_quantity
                        WHERE vk_id=@publ_id';

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@publ_id',   $id);
                $cmd->SetInteger('@new_quantity',  $quantity);
                $cmd->Execute();

            }
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