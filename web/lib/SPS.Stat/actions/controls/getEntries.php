<?php
Package::Load( 'SPS.Stat' );
Package::Load( 'SPS.Site' );
/**
 * addPrice Action
 * @package    SPS
 * @subpackage Stat
 */
class getEntries extends wrapper {

    /**
     * Entry Point
     */
    public function Execute() {
        $userId = Request::getInteger( 'userId' );
        $group  = Request::getString( 'groupName' );
        $offset = Request::getInteger( 'offset' );
        $limit  = Request::getInteger( 'limit' );
        $offset = $offset ? $offset : 0;
        $limit  = $limit  ? $limit  : 25;
        $t1 = 'gr50k';
        $t2 = 'publs50k';
        $t3 = 'publ_rels_names';

        if (isset($group) && isset($userId)) {
            $sql = sprintf('SELECT a.id,a.time,a.quantity,b.ava,b.name, b.price, c.group_name,c.selected_admin
                    FROM
                      %1$s as a
                    INNER JOIN
                      %2$s as b ON a.id=b.vk_id
                    INNER JOIN
                      %3$s as c ON a.id=c.publ_id
                    WHERE
                      c.user_id=%4$d and c.group_name=\'%5$s\'
                    ORDER BY
                      a.id,a.time'
                    , $t1, $t2, $t3, $userId, $group);
        } else {
            $sql = sprintf('SELECT a.id,a.time,a.quantity,b.ava,b.name, b.price
                        FROM
                            %1$s as a
                        INNER JOIN
                            %2$s as b
                        ON
                            a.id=b.vk_id
                        ORDER BY a.id,a.time'
                        ,$t1, $t2);
        }

        $this->db_wrap('query',$sql);
        $rest = $this->q_result;
        $resul = array();
        $i = 0;
        $old_id = '';

        while ($row = $this->db_wrap('get_row', $rest)) {
            $id = $row['id'];

            if ($id != $old_id) {
                $i++;
                $old_id = $id;
            }
//
            if ($i < $offset)
                continue;
            if ($i >= $offset + $limit )
                break;

            $resul[$row['id']]['quantity'][] = $row['quantity'];
            $resul[$row['id']]['name']   = $row['name'];
            $resul[$row['id']]['ava']    = $row['ava'];
            $resul[$row['id']]['time']   = $row['time'];
            $resul[$row['id']]['price']  = $row['price'];
            $resul[$row['id']]['group']  = $row['group_name'];
            $resul[$row['id']]['admins'] = $row['selected_admin'];
        }

        foreach ($resul as $k=>&$v) {
            $v['admins'] = $this->get_admins($k, $v['admins']);
            $time_last = end($v['quantity']);
            $time_comparison = prev($v['quantity']);
            if (count($v['quantity']) > 1  && $time_last != 0 && $time_comparison != 0) {
                $v['diff_abs'] = $time_last - $time_comparison;
                $v['diff_rel'] = round(( $time_last - $time_comparison )  / $time_comparison, 4) * 100 ;
            } else {
                $v['diff_abs'] = '-';
                $v['diff_rel'] = '-';
            }
            $v['quantity'] = $time_last;
        }
        echo ObjectHelper::ToJSON($resul);
    }

    //выбирает админов, в 0 элемент помещает "главного" для этой выборки
    private function get_admins($publ, $sadmin)
    {
        $sql = "select vk_id,role,name,ava,comments from admins where publ_id=" . $publ;
        $this->db_wrap('query', $sql);
        $resul = array();
        $k = '';
        while ($row = $this->db_wrap('get_row')) {
            if ($row['vk_id'] == $sadmin) {
                if (isset($resul[0]))
                    $k = $resul[0];
                $resul[0] = $row;
                if ($k)
                    $resul[] = $k;
            } else
                $resul[] = $row;
        }
        return $resul;
    }
}
?>