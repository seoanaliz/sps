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
        $userId     =   Request::getInteger( 'userId' );
        $groupId    =   Request::getInteger( 'groupId' );
        $offset     =   Request::getInteger( 'offset' );
        $limit      =   Request::getInteger( 'limit' );
        $offset     =   $offset ? $offset : 0;
        $limit      =   $limit  ? $limit  : 25;
        $t1 = 'gr50k';
        $t2 = 'publs50k';
        $t3 = 'publ_rels_names';



        if (isset($groupId) && isset($userId)) {
            $sql = "SELECT a.id,a.time,a.quantity,b.ava,b.name, b.price,c.group_id,c.selected_admin
                    FROM
                      $t1 as a
                    INNER JOIN
                      $t2 as b ON a.id=b.vk_id
                    INNER JOIN
                      $t3 as c ON a.id=c.publ_id
                    WHERE
                      c.user_id=@user_id and c.group_id=@group_id
                    ORDER BY
                      a.id,a.time";

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@user_id', $userId);
            $cmd->SetInteger('@group_id',$groupId);

        } else {
            $sql = "SELECT a.id,a.time,a.quantity,b.ava,b.name, b.price
                    FROM
                        $t1   as a
                    INNER JOIN
                        $t2 as b
                    ON
                        a.id=b.vk_id
                    ORDER BY a.id,a.time";
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        }

        $ds = $cmd->Execute();
        $structure = BaseFactory::getObjectTree( $ds->Columns );
        $resul = array();
        $i = 0;
        $old_id = '';
        $quantity = array();
        $trig = 1;
        $size = $ds->getSize();
        $t = 0;

        while ($ds->next()) {
            $t++;
            $row = $this->get_row($ds, $structure);
            $id = $row['id'];

            if  ($trig) {
                $old_row = $row;
                $trig = 0;
                $old_id = $id;
            }

            if ($id != $old_id || $t == $size) {
                $i++;
                $old_id = $id;
                $admins = $this->get_admins($old_row['id'], $old_row['admins']);
                $groups = array();
                if (isset($userId))
                    $groups = $this->get_groups($old_row['id'], $userId);
                $time_last = end($quantity);
                $time_comparison = prev($quantity);

                if (count($quantity) > 1  && $time_last != 0 && $time_comparison != 0) {
                    $diff_abs = $time_last - $time_comparison;
                    $diff_rel = round(( $time_last - $time_comparison ) / $time_comparison, 4) * 100 ;
                } else {
                    $diff_abs = '0';
                    $diff_rel = '0';
                }

                $quantity = array();
                array_push($resul, array(
                    'id' => $old_row['id'],
                    'quantity'  =>  $time_last,
                    'name'      =>  $old_row['name'],
                    'ava'       =>  $old_row['ava'],
                    'time'      =>  $old_row['time'],
                    'price'     =>  $old_row['price'],
                    'group_id'  =>  $groups,
                    'admins'    =>  $admins,
                    'diff_abs'  =>  $diff_abs,
                    'diff_rel'  =>  $diff_rel
                ));
            }

            $old_row = $row;
            if ($i < $offset)
                continue;
            if ($i >= $offset + $limit )
                break;

            $quantity[] = $row['quantity'];
        }
        echo ObjectHelper::ToJSON(array('response' => $resul));
    }

    //РІС‹Р±РёСЂР°РµС‚ Р°РґРјРёРЅРѕРІ, РІ 0 СЌР»РµРјРµРЅС‚ РїРѕРјРµС‰Р°РµС‚ "РіР»Р°РІРЅРѕРіРѕ" РґР»СЏ СЌС‚РѕР№ РІС‹Р±РѕСЂРєРё
    private function get_row($ds, $structure)
    {

        $res = array();
        foreach($structure as $field) {
            $res[$field] = $ds->getValue($field);
        }
        return $res;
    }

    private function get_admins($publ, $sadmin)
    {
        $resul = array();
        $sql = "select vk_id,role,name,ava,comments from admins where publ_id=@publ_id";
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@publ_id',   $publ);
        $ds = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        while ( $ds->next() ) {
            $vk_id = $ds->getValue('vk_id', TYPE_INTEGER);
            if ($vk_id == $sadmin){
                if (isset($resul[0]))
                    $k = $resul[0];

                $resul[0] = $this->get_row($ds, $structure);

                if ($k)
                    $resul[] = $k;
            } else
                 $resul[] = $this->get_row($ds, $structure);
        }

        return $resul;
    }

    private function get_groups($publId, $userId)
    {
        $groups = array();
        $sql = "select group_id from publ_rels_names where publ_id=@publ_id AND user_id=@user_id";

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger('@publ_id',   $publId);
        $cmd->SetInteger('@user_id',  $userId);
        $ds = $cmd->Execute();
        while ( $ds->next() ){
            $groups[] = $ds->getValue('group_id', TYPE_INTEGER);
        }
        return $groups;
    }
}
?>