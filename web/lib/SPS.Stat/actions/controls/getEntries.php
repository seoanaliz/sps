<?php
Package::Load( 'SPS.Stat' );
Package::Load( 'SPS.Site' );
/**
 * addPrice Action
 * @package    SPS
 * @subpackage Stat
 */

set_time_limit(10);
class getEntries {

    /**
     * Entry Point
     */

    const T_PUBLICS_POINTS = 'gr50k';
    const T_PUBLICS_LIST   = 'publs50k';
    const T_PUBLICS_RELS   = 'publ_rels_names';

    public function Execute() {
        error_reporting( 0 );
        $userId     =   Request::getInteger( 'userId' );
        $groupId    =   Request::getInteger( 'groupId' );
        $offset     =   Request::getInteger( 'offset' );
        $limit      =   Request::getInteger( 'limit' );
        $search     =   pg_escape_string(Request::getString( 'search' ));
        $sortBy     =   pg_escape_string(Request::getString( 'sortBy' ));
        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $offset     =   $offset ? $offset : 0;
        $limit      =   $limit  ?  $limit  :   25;
        $sortBy     =   $sortBy ? $sortBy  : ' diff_abs ';
        $sortReverse = $sortReverse? ' DESC ': '';

        if (isset($groupId) && isset($userId)) {
            $search = $search ? " AND a.name ILIKE '%" . $search . "%' ": '';

            $sql = 'SELECT
                        a.vk_id,a.ava,a.name,a.price,a.diff_abs,a.diff_rel,a.quantity,b.selected_admin
                    FROM
                        ' . self::T_PUBLICS_LIST . ' as a,' . self::T_PUBLICS_RELS . ' as b
                    WHERE
                        b.group_id=@group_id AND b.publ_id=a.vk_id AND b.user_id=@user_id '. $search . '
                    ORDER BY '
                        . $sortBy . $sortReverse .
                  ' OFFSET '
                        . $offset .
                  ' LIMIT '
                        . $limit;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
            $cmd->SetInteger('@group_id', $groupId);
            $cmd->SetInteger('@user_id', $userId);

        } else {
            $search     =   $search ? " WHERE name ILIKE '%" . $search . "%' ": '';
            $sql = 'SELECT
                        vk_id,ava,name,price,diff_abs,diff_rel,quantity
                    FROM '
                        . self::T_PUBLICS_LIST .
                    $search .
                  ' ORDER BY '
                        . $sortBy . $sortReverse .
                  ' OFFSET '
                        . $offset .
                  ' LIMIT '
                        . $limit;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );

            $cmd->SetString('@sortBy', $sortBy);
        }

        $ds = $cmd->Execute();
        $structure = BaseFactory::getObjectTree( $ds->Columns );
        $resul = array();

        while ($ds->next()) {
            $row = $this->get_row($ds, $structure);

            $admins = $this->get_admins($row['vk_id'], $row['selected_admin']);
            $groups = array();
            if (isset($userId)) {
                $groups = $this->get_groups($row['vk_id'], $userId);
            }

            $resul[] =  array(
                                'id'        =>  $row['vk_id'],
                                'quantity'  =>  $row['quantity'],
                                'name'      =>  $row['name'],
                                'ava'       =>  $row['ava'],
                                'price'     =>  $row['price'],
                                'group_id'  =>  $groups,
                                'admins'    =>  $admins,
                                'diff_abs'  =>  $row['diff_abs'],
                                'diff_rel'  =>  $row['diff_rel']
                            );
        }
        echo ObjectHelper::ToJSON(array('response' => $resul));
    }


    private function get_row($ds, $structure)
    {

        $res = array();
        foreach($structure as $field) {
            $res[$field] = $ds->getValue($field);
        }
        return $res;
    }

    //выбирает админов, в 0 элемент помещает "главного" для этой выборки
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
        $cmd->SetInteger('@user_id',  $userId);
        $cmd->SetInteger('@publ_id',   $publId);
        $ds = $cmd->Execute();
        while ( $ds->next() ) {
            $groups[] = $ds->getValue('group_id', TYPE_INTEGER);
        }
        return $groups;
    }



}


?>