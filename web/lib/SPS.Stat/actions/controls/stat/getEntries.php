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

    private $conn;
    /**
     * Entry Point
     */

    public function Execute()
    {
        error_reporting( 0 );
        $this->conn =   ConnectionFactory::Get('tst');
        $userId     =   Request::getInteger( 'userId' );
        $groupId    =   Request::getInteger( 'groupId' );
        $offset     =   Request::getInteger( 'offset' );
        $limit      =   Request::getInteger( 'limit' );
        $quant_max  =   Request::getInteger( 'max' );
        $quant_min  =   Request::getInteger( 'min' );
        $period     =   Request::getInteger( 'period' );//
        $group_type =   Request::getInteger( 'groupType');
        $search     =   trim(pg_escape_string( Request::getString( 'search' )));
        $sortBy     =   pg_escape_string( Request::getString( 'sortBy' ));
        $time_from  =   Request::getInteger( 'timeFrom' );
        $time_to    =   Request::getInteger( 'timeTo' );
        $page       =   Request::getInteger( 'page' );

        if( $time_to == 0 )
            $time_to = time();

        $sortReverse    =   Request::getInteger( 'sortReverse' );
        $show_in_mainlist = Request::getInteger( 'show' );
        $page           =   $page ? ' AND publ.page=true ' : ' ';
        $quant_max      =   $quant_max ? $quant_max : 100000000;
        $quant_min      =   $quant_min ? $quant_min : 0;
        $offset         =   $offset ? $offset : 0;
        $limit          =   $limit  ?  $limit : 25;
        $search         =   mb_strlen( $search ) > 5 ? mb_substr( $search, 0, mb_strlen( $search ) - 2 ) : $search;

        $group  = StatGroups::get_group($groupId);
        //1 тип статистики
        if ( empty( $group) || $group['type'] != 2 ) {
            $allowed_sort_values = array('diff_abs', 'quantity', 'diff_rel', 'visitors', 'active', 'in_search' );
            $sortBy  = $sortBy && in_array( $sortBy, $allowed_sort_values, 1 )  ? $sortBy  : 'diff_abs';
            $show_in_mainlist = $show_in_mainlist && !$groupId ? ' AND sh_in_main = TRUE ' : '';

            if ( $period == 7 ) {
                if ( $sortBy == 'diff_abs' )
                    $sortBy   .= '_week';
                $diff_rel = 'diff_rel_week';
                $diff_abs = 'diff_abs_week';
                $diff_vis = 'diff_vis_week';
                $visitors = 'visitors_week';
            } else if( $period == 30 ) {
                if ( $sortBy == 'diff_abs' )
                    $sortBy   .= '_month';
                $diff_rel = 'diff_rel_month';
                $diff_abs = 'diff_abs_month';
                $diff_vis = 'diff_vis_month';
                $visitors = 'visitors_month';
            } else {
                $diff_rel = 'diff_rel';
                $diff_abs = 'diff_abs';
                $diff_vis = 'diff_vis';
                $visitors = 'visitors';
            }
            $sortBy  = $sortBy  .  (( $sortReverse? '' : ' DESC ') . ' NULLS LAST ');

            if ( isset( $groupId ) ) {
                $search = $search ? " AND publ.name ILIKE '%" . $search . "%' " : '';

             $sql = 'SELECT
                        publ.vk_id, publ.ava, publ.name, publ.price, publ.' . $diff_abs . ',
                        publ.' . $diff_rel . ', publ.' . $visitors . ',  publ.quantity, gprel.main_admin,
                        publ.in_search,publ.active
                    FROM
                            ' . TABLE_STAT_PUBLICS . ' as publ,
                            ' . TABLE_STAT_GROUP_PUBLIC_REL . ' as gprel
                    WHERE
                          publ.vk_id=gprel.public_id '
                          . $page .
                         ' AND gprel.group_id=@group_id
                          AND publ.quantity >= @min_quantity
                          AND publ.quantity <= @max_quantity
                          AND publ.quantity >= 50000
                          ' . $search . '
                    ORDER BY '
                        . $sortBy . #$sortReverse .
                  ' OFFSET '
                        . $offset .
                  ' LIMIT '
                        . $limit;

                    $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                    $cmd->SetInteger('@group_id', $groupId);
                    $cmd->SetInteger('@user_id',  $userId);
//                    echo $cmd->GetQuery() . '<br>';
            } else {
                $search   =   $search ? "AND name ILIKE '%" . $search . "%' ": '';

                $sql = 'SELECT
                            vk_id, ava, name, price, ' . $diff_abs . ', ' . $diff_rel . ',' . $visitors . ', quantity,in_search,active
                        FROM '
                            . TABLE_STAT_PUBLICS . ' as publ
                        WHERE
                            quantity > @min_quantity '
                            . $page .
                          ' AND quantity < @max_quantity
                            AND quantity > 50000'.
                            $search . $show_in_mainlist .
                      ' ORDER BY '
                            . $sortBy . #$sortReverse .
                      ' OFFSET '
                            . $offset .
                      ' LIMIT '
                            . $limit;
                $cmd = new SqlCommand( $sql, $this->conn );

                $cmd->SetString('@sortBy', $sortBy);
//                echo $cmd->GetQuery() . '<br>';

            }
            $cmd->SetInteger('@min_quantity', $quant_min);
            $cmd->SetInteger('@max_quantity', $quant_max);
            $ds = $cmd->Execute();
            $structure = BaseFactory::getObjectTree( $ds->Columns );
            $resul = array();
            while ($ds->next()) {
                $row = $this->get_row( $ds, $structure );
                $admins = array();
//                if ( isset( $row[ 'main_admins' ]))
                $admins = $this->get_admins( $row['vk_id'], $row['main_admin'] );
                $groups = array();
                if ( isset( $userId )) {
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
                                'diff_rel'  =>  $row[$diff_rel],
                                'visitors'  =>  $row[$visitors],
                                'in_search' =>  $row['in_search'] == 't' ? 1 : 0,
                                'active'    =>  $row['active']== 't' ? true : false
                );
            }
        }
        //2 тип, наши паблики. Сортировка силами php
        else {
            $allowed_sort_values = array(   'views',
                                            'overall_posts',
                                            'posts_days_rel',
                                            'sb_posts_count',
                                            'sb_posts_rate',
                                            'auth_posts',
                                            'auth_likes_eff',
                                            'auth_reposts_eff',
                                            'visitors',
                                            'abs_vis_grow',
                                            'rel_vis_grow'
            );
            $resul = $this->get_our_publics_state( $time_from, $time_to );
            $sortBy  = $sortBy && in_array( $sortBy, $allowed_sort_values, 1 )  ? $sortBy  : 'visitors';
            $a = $this->compare( $sortBy, $sortReverse );
            usort( $resul, $a );
        }

        echo ObjectHelper::ToJSON(array(
                                        'response' => array(
                                                            'list'       =>  $resul,
                                                            'min_max'    =>  $this->get_min_max(),
                                                            'group_type' =>  empty($group) ? null : $group['type']
                                                            )
                                        )
                                    );
    }

    private function get_visitors( $public_id, $period )
    {
        switch ( $period ){
            case 7:
                $interval = 'interval \'1 week\'';
                break;
            case 30:
                $interval = 'interval \'1 month\'';
                break;
            default:
                $interval = 'interval \'1 day\'';
        }
        $sql = 'SELECT visitors FROM ' . TABLE_STAT_PUBLICS_POINTS .
              ' WHERE
                    id=@publ_id
                    and time = CURRENT_DATE - ' . $interval . '
                ORDER BY time DESC';

        $cmd = new SqlCommand( $sql, $this->conn );

        $cmd->SetInteger('@publ_id', $public_id);
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->GetInteger( 'visitors');
    }

    private function get_row( $ds, $structure )
    {
        $res = array();
        foreach( $structure as $field ) {
            $res[ $field ] = $ds->getValue( $field );
        }
        return $res;
    }

    //возвращает данные о наших пабликах
    private function get_our_publics_state( $time_start, $time_stop )
    {
        $publics = StatPublics::get_our_publics_list();
        $res = array();
        $ret = array();

        foreach( $publics as $public ) {
            $res['ava'] = $this->get_ava($public['id']);
            $res['id']  = $public['id'];
            $res['name']= $public['title'];
            $authors_posts      =   StatPublics::get_public_posts( $public['sb_id'], 'authors', $time_start, $time_stop );
            $non_authors_posts  =   StatPublics::get_public_posts( $public['sb_id'], 'sb', $time_start, $time_stop );
            $ad_posts           =   StatPublics::get_public_posts( $public['sb_id'], 'ads', $time_start, $time_stop );
            $posts_quantity     =   $authors_posts['count'] + $non_authors_posts['count'];
            //всего постов
            $res['overall_posts'] = $posts_quantity;
            $days = round(( $time_stop - $time_start ) / 84600 );
            $res['posts_days_rel'] = round( $posts_quantity / $days );

            //постов из источников
            $res['sb_posts_count'] = $non_authors_posts['count'];
            // средний rate спарсенных постов
            $res['sb_posts_rate']   = StatPublics::get_average_rate( $public['sb_id'], $time_start, $time_stop );
            //todo главноредакторских постов непосредственно на стену, гемор!!!!! <- в демона
            $res['auth_posts']      = $posts_quantity ?
                round( 100 * $authors_posts['count'] / $posts_quantity   )  : 0 ;
            $res['ad_posts_count']  =   $ad_posts['count'];
            $res['auth_likes_eff']  = $non_authors_posts['likes'] ?
                ((round( $authors_posts['likes'] / $non_authors_posts['likes'], 4 ) * 100) ) : 0;
            $res['auth_reposts_eff']= $non_authors_posts['reposts'] ?
                (round( $authors_posts['reposts'] / $non_authors_posts['reposts'], 4 ) * 100 ) : 0;

            //прирост подписчиков относительно предыдущего периода
            $sub_now = StatPublics::get_avg_subs_growth( $public['sb_id'], $time_start, $time_stop );
            $sub_pre = StatPublics::get_avg_subs_growth( $public['sb_id'], ( 2 * $time_start - $time_stop ), $time_start );

            //прирост посетителей относительно предыдущего периода
            $vis_now = StatPublics::get_average_visitors( $public['sb_id'], $time_start, $time_stop );
            $vis_prev_period = StatPublics::get_average_visitors( $public['sb_id'], ( 2 * $time_start - $time_stop ), $time_start );
            if ( $vis_now && $vis_prev_period ) {
                $abs_vis_grow = $vis_now - $vis_prev_period;
                $rel_vis_grow = round( $abs_vis_grow * 100 / $vis_prev_period, 2 );
            } else {
                $rel_vis_grow = 0;
                $abs_vis_grow = 0;
            }

            $res['rel_vis_grow']  = $rel_vis_grow;
            $res['abs_vis_grow']  = $abs_vis_grow;
//            print_r
//            $guests = StatPublics::get_views_visitors_from_base( $public['sb_id'], $time_start, $time_stop );
//            $res['visitors']       = $guests['visitors'];
//            $res['avg_vis_grouth'] = $guests['vis_grouth'];
//            $res['views']          = $guests['views'];
//            $res['avg_vie_grouth'] = $guests['vievs_grouth'];
            $ret[] = $res;

        }
        return $ret;
    }

    private function compare( $field, $rev )
    {
        $rev = $rev ? 1 : -1 ;
        $code = "
        if ( $rev == 0 && \$a['$field'] == null ) return  1;
        if ( $rev == 0 && \$b['$field'] == null ) return -1;
        return  $rev * strnatcmp(\$a['$field'], \$b['$field']);";
        return create_function('$a,$b', $code );
    }

    private function get_ava( $public_id )
    {
        $sql = 'SELECT ava
                FROM ' . TABLE_STAT_PUBLICS .
               ' WHERE vk_id=@publ_id';
        $cmd = new SqlCommand( $sql, $this->conn );
        $cmd->SetInteger( '@publ_id', $public_id);
        $ds = $cmd->Execute();
        $ds->Next();
        return $ds->getValue('ava');
    }

    //выбирает админов, в 0 элемент помещает "главного" для этой выборки
    private function get_admins( $publ, $sadmin ='' )
    {
        $resul = array();
        $sql = "select vk_id,role,name,ava,comments from " . TABLE_STAT_ADMINS . " where publ_id=@publ_id";
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetInteger( '@publ_id',  $publ );
        $ds = $cmd->Execute();
        $structure  = BaseFactory::getObjectTree( $ds->Columns );
        while ( $ds->next()) {
            $vk_id = $ds->getValue( 'vk_id', TYPE_INTEGER );
            if ( $vk_id == $sadmin ) {
                if ( isset( $resul[0] ) )
                    $k = $resul[0];

                $resul[0] = $this->get_row($ds, $structure);

                if ( $k )
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

    private function get_min_max()
    {
        $sql = 'SELECT MIN(quantity), MAX(quantity)  FROM ' . TABLE_STAT_PUBLICS . ' WHERE quantity > 50000' ;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $ds->Next();
        return array(
                        'min'  =>   $ds->getValue('min'),
                        'max'  =>   $ds->getValue('max')
        );
    }
}
?>