<?php
//Package::Load( 'SPS.Stat' );
//Package::Load( 'SPS.Site' );
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

    public function Execute() {

        error_reporting( 0 );
        $userId     =   Request::getInteger( 'userId' );
        $groupId    =   Request::getInteger( 'groupId' );
        $offset     =   Request::getInteger( 'offset' );
        $limit      =   Request::getInteger( 'limit' );
        $quant_max  =   Request::getInteger( 'max' );
        $quant_min  =   Request::getInteger( 'min' );
        $period     =   Request::getInteger( 'period' );

        $search     =   pg_escape_string(Request::getString( 'search' ));
        $sortBy     =   pg_escape_string(Request::getString( 'sortBy' ));

        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $show_in_mainlist = Request::getInteger( 'show' );

        $quant_max      =   $quant_max ? $quant_max : 100000000;
        $quant_min      =   $quant_min ? $quant_min : 0;
        $offset         =   $offset ? $offset : 0;
        $limit          =   $limit  ?  $limit  :   25;

        $allowed_sort_values = array('diff_abs', 'quantity', 'diff_rel' );
        $sortBy  = $sortBy && in_array($sortBy, $allowed_sort_values, 1)  ? $sortBy  : 'diff_abs';

        $sortReverse    =   $sortReverse? '' : ' DESC ';
        $show_in_mainlist = $show_in_mainlist && !$groupId ? ' AND sh_in_main = TRUE ' : '';


        if ( $period == 7 ) {
            if ( $sortBy == 'diff_abs' )
                $sortBy = 'diff_abs_week';
            if ( $sortBy == 'diff_rel' )
                $sortBy = 'diff_rel_week';
            $diff_rel = 'diff_rel_week';
            $diff_abs = 'diff_abs_week';
        } else if( $period == 30 ) {
            if ( $sortBy == 'diff_abs' )
                $sortBy = 'diff_abs_month';
            if ( $sortBy == 'diff_rel' )
                $sortBy = 'diff_rel_month';
            $diff_rel = 'diff_rel_month';
            $diff_abs = 'diff_abs_month';
        } else {
            $diff_rel = 'diff_rel';
            $diff_abs = 'diff_abs';
        }

        if ( isset( $groupId ) ) {
            $search = $search ? " AND publ.name ILIKE '%" . $search . "%' " : '';


        $sql = 'SELECT
                    publ.vk_id, publ.ava, publ.name, publ.price, publ.' . $diff_abs . ',
                    publ.' . $diff_rel . ', publ.quantity, gprel.main_admin
                FROM
                        ' . TABLE_STAT_PUBLICS . ' as publ,
                        ' . TABLE_STAT_GROUP_PUBLIC_REL . ' as gprel
                WHERE
                      publ.vk_id=gprel.public_id
                      AND gprel.group_id=@group_id
                      AND publ.quantity >= @min_quantity
                      AND publ.quantity <= @max_quantity
                      ' . $search . '
                ORDER BY '
                    . $sortBy . $sortReverse .
              ' OFFSET '
                    . $offset .
              ' LIMIT '
                    . $limit;

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id', $groupId);
                $cmd->SetInteger('@user_id',  $userId);

        } else {
            $search   =   $search ? "AND name ILIKE '%" . $search . "%' ": '';

            $sql = 'SELECT
                        vk_id, ava, name, price, ' . $diff_abs . ', ' . $diff_rel . ', quantity
                    FROM '
                        . TABLE_STAT_PUBLICS . ' as publ
                    WHERE
                        quantity > @min_quantity
                        AND quantity < @max_quantity '.
                        $search . $show_in_mainlist .
                  ' ORDER BY '
                        . $sortBy . $sortReverse .
                  ' OFFSET '
                        . $offset .
                  ' LIMIT '
                        . $limit;
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );

            $cmd->SetString('@sortBy', $sortBy);
        }
        $cmd->SetInteger('@min_quantity', $quant_min);
        $cmd->SetInteger('@max_quantity', $quant_max);
        $ds = $cmd->Execute();
        $structure = BaseFactory::getObjectTree( $ds->Columns );
        $resul = array();

        while ($ds->next()) {
            $row = $this->get_row( $ds, $structure );

            $admins = $this->get_admins($row['vk_id'], $row['main_admin'] );
            $groups = array();
            if ( isset( $userId ) ) {
                $groups = $this->get_groups( $userId, $row['vk_id'] );
            }
            $resul[] =  array(
                                'id'        =>  $row['vk_id'],
                                'quantity'  =>  $row['quantity'],
                                'name'      =>  $row['name'],
                                'ava'       =>  $row['ava'],
                                'price'     =>  $row['price'],
                                'group_id'  =>  $groups,
                                'admins'    =>  $admins,
                                'diff_abs'  =>  $row[$diff_abs],
                                'diff_rel'  =>  $row[$diff_rel]
                            );
        }


        echo ObjectHelper::ToJSON(array(
                                        'response' => array(
                                                            'list'      =>  $resul,
                                                            'min_max'   =>  $this->get_min_max()
                                                            )
                                        )
                                    );
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
        $sql = "select vk_id,role,name,ava,comments from " . TABLE_STAT_ADMINS . " where publ_id=@publ_id";
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger( '@publ_id',  $publ );
        $ds = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        while ( $ds->next() ) {
            $vk_id = $ds->getValue( 'vk_id', TYPE_INTEGER );
            if ( $vk_id == $sadmin ) {
                if ( isset( $resul[0] ) )
                    $k = $resul[0];

                $resul[0] = $this->get_row($ds, $structure);

                if ($k)
                    $resul[] = $k;
            } else
                 $resul[] = $this->get_row($ds, $structure);
        }

        return $resul;
    }

    private function get_groups( $userId, $public_id )
    {
        $groups = array();
        $sql = "SELECT a.group_id from "
                   . TABLE_STAT_GROUP_USER_REL   . " AS a,
                 " . TABLE_STAT_GROUP_PUBLIC_REL . " AS b
                 WHERE
                        a.group_id=b.group_id
                    AND user_id=@user_id
                    AND b.public_id=@public_id";


        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger( '@user_id',  $userId );
        $cmd->SetInteger( '@public_id',  $public_id );
        $ds = $cmd->Execute();
        while ( $ds->next() ) {
            $groups[] = $ds->getValue('group_id', TYPE_INTEGER);
        }
        return $groups;
    }

    private function get_difference($current_quantity, $period, $public_id ) {

        $sql = 'SELECT MAX(time) FROM ' . TABLE_STAT_PUBLICS_POINTS;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst') );
        $ds = $cmd->Execute();
        $ds->Next();
        $time_max = $ds->getValue('max', TYPE_INTEGER);

        $time_b = $time_max - $period * 24 * 60 * 60;
        $sql = 'SELECT quantity FROM ' . TABLE_STAT_PUBLICS_POINTS . ' WHERE id=@public_id AND time=@time';

        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst') );
        $cmd->SetString('@time', $time_b);
        $cmd->SetInteger('@public_id', $public_id);
        $ds = $cmd->Execute();
        $ds->Next();

        $quantity = $ds->getValue('quantity', TYPE_INTEGER);
        if (!$quantity)
            return array (
                'diff_rel'  =>  '-',
                'diff_abs'  =>  '-'
            );



        return array (
                        'diff_rel'  =>  round( ($current_quantity / $quantity - 1) * 100, 2 ),
                        'diff_abs'  =>  $current_quantity - $quantity
                     );


    }

    private function get_min_max()
    {
        $sql = 'SELECT MIN(quantity), MAX(quantity)  FROM ' . TABLE_STAT_PUBLICS ;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst') );
        $ds = $cmd->Execute();
        $ds->Next();
        return array(
                        'min'  =>   $ds->getValue('min'),
                        'max'  =>   $ds->getValue('max')
        );
    }


}


?>