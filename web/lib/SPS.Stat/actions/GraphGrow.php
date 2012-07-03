<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );
    Package::Load( 'SPS.Stat' );

    class GraphGrow extends wrapper 
    {
         public function execute()
         {
            $this->db_wrap('connect');
            if (isset($_REQUEST['p']) && $_REQUEST['p'] == 'our') {
                $t1 = 'our_publs_points' ;
                $t2 = 'our_publs2';
            } else {
               /* echo 'HERE';*/
                $t1 = 'gr50k' ;
                $t2 = 'publs50k';
            }
       
//        $sql = 'SET NAMEs utf8';
//        $a = $this->db_wrap('query',$sql);

            $sql = "SELECT $t1.id,$t1.time,$t1.quantity,$t2.ava,$t2.name
                    FROM
                        $t1
                    INNER JOIN
                        $t2
                    ON
                        $t1.id=$t2.vk_id
                    ORDER BY $t1.time
                ";
//            $sql = 'select * from gr50k, publs50k where gr50k.id=publs50k.vk_id';
           /* echo $sql . '<br>';*/
        $res = $this->db_wrap('query',$sql);
         
        $resul = array();
        $time_for_table = 0;
        $rest = $this->q_result;
        while ($row = $this->db_wrap('get_row', $rest)) {
//            print_r($row);
            $resul[$row['id']]['quantity'][] = $row['quantity'];
            $resul[$row['id']]['name'] = $row['name'];
            $resul[$row['id']]['ava']  = $row['ava'];
            $resul[$row['id']]['time'] = $row['time'];
            $sql = "select * from admins where publ_id={$row['id']}";
            /*echo $sql;*/
            $res = $this->db_wrap('query',$sql);
            $resul[$row['id']]['admins'] = array();
            while ($row2 = $this->db_wrap('get_row')) {
                /*print_r($row2);*/
                $resul[$row['id']]['admins'][] = $row2;
            }
            
            if (+$row['time'] > +$time_for_table) {
                $time_for_table = +$row['time'];
            }
        }

        echo '<table id="tbld" class="tablesorter"  >
                <thead>
                <tr>
                    <th>Паблик</th>
                    <th>Название</th>
                    <th>' . 'на дату ' . date('d-m-Y', $time_for_table)  . '</th>
                    <th>Процент роста</th>
                    <th>Администраторы</th>
                </tr>
                </thead>
                <tbody>';

        foreach($resul as $publ => $dt) {
           //todo - РїРѕСЃР»РµРґРЅРµРµ Рё РЅСѓР¶РЅРѕРµ Р·РЅР°С‡РµРЅРёСЏ С‡РµРєР°С‚СЊ!
            $time_last = end($dt['quantity']);
            $time_comparison = prev($dt['quantity']);
            if(!$time_comparison)
                $time_comparison = $time_last;
            if (!$time_comparison || !$time_last) continue;
            echo '<tr>';
            $img = "<img src=\"{$dt['ava']}\" href=\"http://vk.com/public$publ\"/>";
            $ad_line = '';
            if (count($dt['admins']) > 0) {
                foreach ($dt['admins'] as $admin) {
                    $ad_line .= "<a href=\"http://vk.com/id{$admin['vk_id']}\"><img width=50 src=\"{$admin['ava']}\" title=\"{$admin['name']} {$admin['role']}\"  /></a><br>";
                }
            }
            
            $name = "<a  href=\"http://vk.com/public$publ\">{$dt['name']}</a>";
            echo "<td>$img<td>$name  <td> $time_last<td>" . round($time_last*100/$time_comparison-100, 2).'%<td>' .( $time_last - $time_comparison).'<td>'.$ad_line.'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    }
?>
