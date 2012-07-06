<?php
/*    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );*/
    Package::Load( 'SPS.Stat' );

    class GraphGrow extends wrapper
    {
         public function execute()
         {

            if (isset($_REQUEST['p']) && $_REQUEST['p'] == 'our') {
                $t1 = 'our_publs_points' ;
                $t2 = 'our_publs2';
            } else {
               /* echo 'HERE';*/
                $t1 = 'gr50k' ;
                $t2 = 'publs50k';
            }
             $page  = Request::getInteger( 'page' );
             $page = $page ? $page: 1;
             $group = Request::getString( 'group' );
             echo $group . '<br>';
             if ($group) {
                 $sql = "SELECT $t1.id,$t1.time,$t1.quantity,$t2.ava,$t2.name, $t2.price, $t2.group
                        FROM
                            $t1
                        INNER JOIN
                            $t2
                        ON
                            $t1.id=$t2.vk_id
                        WHERE
                           $t2.group=$group
                        ORDER BY $t1.time
                    ";
            } else {
                 $sql = "SELECT $t1.id,$t1.time,$t1.quantity,$t2.ava,$t2.name, $t2.price, $t2.group
                        FROM
                            $t1
                        INNER JOIN
                            $t2
                        ON
                            $t1.id=$t2.vk_id
                        ORDER BY $t1.time
                    ";
            }

//            $sql = 'select * from gr50k, publs50k where gr50k.id=publs50k.vk_id';
           /* echo $sql . '<br>';*/
            $this->db_wrap('query',$sql);

            $resul = array();
            $time_for_table = 0;
            $rest = $this->q_result;
            $start = $page * 50 - 50;
            $i = 0;
             while ($row = $this->db_wrap('get_row', $rest)) {
                $i++;
                if ($i <= $start)
                    continue;
                if ($i > $start + 50)
                    continue;
                $resul[$row['id']]['quantity'][] = $row['quantity'];
                $resul[$row['id']]['name'] = $row['name'];
                $resul[$row['id']]['ava']  = $row['ava'];
                $resul[$row['id']]['time'] = $row['time'];
                $resul[$row['id']]['price'] = $row['price'];
                $sql = "select * from admins where publ_id=" . $row['id'];
                $this->db_wrap('query',$sql);
                $resul[$row['id']]['admins'] = array();
                while ($row2 = $this->db_wrap('get_row')) {
                    $resul[$row['id']]['admins'][] = $row2;
                }
                if (+$row['time'] > +$time_for_table) {
                    $time_for_table = +$row['time'];
                }
             }

             Response::setInteger( 'pages', round($i/50,0));
             Response::setInteger( 'last_time', date("d.m.Y", $time_for_table));
             Response::setArray( 'big_publics_array', $resul );
    }

    }
?>
