<?php
    Package::Load( 'SPS.Stat' );

    set_time_limit(1600);
    //
    class WrPoints extends wrapper
    {
        const BASE_RENEW = false;
        private $ids = '';

    public function Execute()
    {
            $this->ids = $this->get_publics();
            $this->points();
    }


    //возвращает список лайкнувших(сделавших репост при $retweets=true) пост
    public function get_likes($post_id, $retweets = false)
    {

        $post_id = explode('_', $post_id);

        $ununique_ids = array();


        $params = array (
                'type'      =>  'post',
                'owner_id'  =>  "-$post_id[0]",
                'item_id'   =>  "$post_id[1]",
                'count'     =>  1000
        );
        if ($retweets) {
            $params['filter']   =  'copies';
        }
        $offset = 0;
        while ( 1 ) {
            $params['offset'] = $offset;
            $res = $this->vk_api_wrap('likes.getList', $params);
            sleep(0.3);
            $quan = count($res->users);
            if ($quan < 3)
                break;

            $ununique_ids = array_merge($ununique_ids, $res->users);

            if ($quan > 998) {
                echo "next ($res->count > $offset )<br>";
                $offset += 1000;
            } else {
                break;
            }
        }

        return $ununique_ids;

    }
//возвращает список
    public function get_comments($post_id)
    {

        $post_id = explode('_', $post_id);

        $ununique_reps = array();


        $params = array (
                'owner_id'  =>  "-$post_id[0]",
                'post_id'   =>  "$post_id[1]",
                'count'     =>  100,
                'preview_length'    =>  1
        );

        $offset = 0;
        while (1) {

            $params['offset'] = $offset;
            $res = $this->vk_api_wrap('wall.getComments', $params);
            $quan = $res[0];
            unset($res[0]);
            echo $quan.'<br>';
            $arr = array();
            foreach ($res as $comm) {
                $arr[] = $comm->uid;
            }

            sleep(0.3);

            if ($quan < 1)
                break;

            $ununique_reps = array_merge($ununique_reps, $arr);

            if (+$quan > $offset) {
                echo "next ($quan > $offset )<br>";
                $offset += 100;
            } else {
                break;
            }
        }

        return $ununique_reps;
    }

    public function points()
    {
        print_r($this->ids);
        foreach ($this->ids as $id) {
            echo '<br>id = ' . $id[0] . '<br>';
            $sql = "SELECT id FROM publics WHERE vk_id=$id[0] AND active=1";
            $res = $this->db_wrap('query', $sql);

            $id_inner = $this->db_wrap('get_row');
            $id_inner = $id_inner['id'];

            if (self::BASE_RENEW)
                $date_from = 1333720301;
            else {
                $sql = 'SELECT MAX(date_point) AS max_date FROM points WHERE publ_inner_id=' . $id_inner;
                $res = $this->db_wrap('query', $sql);
                $row = $this->db_wrap('get_row');
                $date_from = $row['max_date'];
                if ($date_from == '')
                    $date_from = 0;
//                    continue;
            }
            $date_to = time() - 86400;

            $sql = "SELECT * FROM posts_for_likes WHERE vk_id LIKE '" . $id[0] . "_%' AND time_st>$date_from AND time_st<$date_to ORDER BY time_st";
            echo "<br> $sql <br> ";


            if (!$this->db_wrap('query', $sql)) {
                echo "Р·Р°РїРёСЃРµР№ РјРµР¶РґСѓ $date_to Рё $date_from  РЅРµ РЅР°С€Р»РѕСЃСЊ";
                continue;
            }
            $likes = $reposts = $comments = 0;
            $unic_likes = array();
            $unic_retweets = array();
            $unic_comms = array();
            $day = '';
            $values = '';

            while($row = $this->db_wrap('get_row')) {
                $new_day = date('d m Y', $row['time_st']);
                if ($new_day != $day && $day != '') {

                    $month  = date('m', $row['time_st']);
                    $year   = date('Y', $row['time_st']);
                    $dd     = date('d', $row['time_st']);
                    $day    = mktime(0, 0, 0, $month, $dd, $year);
                    echo "Р·Р°РїРёСЃСЊ ! day $day, $likes, $reposts, $comments <br>";
                    echo ("РІСЃРµРіРѕ РЅРµСѓРЅРёРєР°Р»СЊРЅС‹С…: " . count($unic_likes) . '<br>');
                    $u_l =  count(array_unique($unic_likes));
                    $u_r =  count(array_unique($unic_retweets));
                    $u_c =  count(array_unique($unic_comms));
                    echo ("РІСЃРµРіРѕ СѓРЅРёРєР°Р»СЊРЅС‹С…: " . $q . '<br>');

//                    $values .= $id_inner . "," . $day . "," . $likes . "," . $reposts . "," . $comments . "),(" ;
                    $sql = "INSERT INTO
                        points(publ_inner_id,date_point,likes,reposts,comments, unic_likes, unic_reposts, unic_comms)
                    VALUES
                        ($id_inner,$day,$likes,$reposts,$comments,$u_l,$u_r,$u_c)";
//                    ON DUPLICATE KEY UPDATE
//                        likes=likes+$likes,reposts=reposts+$reposts,comments=comments+$comments";
                    $this->db_wrap('query', $sql);
                    $day = $new_day;
                    $likes = $reposts = $comments = 0;
                    $unic_likes = array();
                    $unic_retweets = array();
                    $unic_comms = array();
                    continue;
                } elseif ($day == '')
                    $day = $new_day;
                $likes      += $row['likes'];
                $reposts    += $row['reposts'];
                $comments   += $row['comments'];
                $unic_likes = array_merge($unic_likes, $this->get_likes($row['vk_id']));
                $unic_retweets = array_merge($unic_retweets, $this->get_likes($row['vk_id'], true));
                $unic_comms = array_merge($unic_comms, $this->get_comments($row['vk_id']));
                echo $unic_likes . '<br>';
            }

//            $values = '('.trim($values, ',(');
//            if (strlen($values) < 2) {
//                echo "Р·Р°РїРёСЃРµР№ РјРµР¶РґСѓ $date_to Рё $date_from  РЅРµ РЅР°С€Р»РѕСЃСЊ";
//                return false;
//            }
//            $sql = "INSERT ignore INTO points(publ_inner_id,date_point,likes,reposts,comments) VALUES $values ";
//            $res = $this->db_wrap('query', $sql);
//            echo '<br>' . $values . '<br>';
        }
    }

}



?>