<?php
/**
 * addPrice Action
 * @package    SPS
 * @subpackage Stat
 */
new stat_tables();
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
        $user_id    =   AuthVkontakte::IsAuth();
        $group_id   =   Request::getString( 'groupId' );
        $offset     =   Request::getInteger( 'offset' );
        $limit      =   Request::getInteger( 'limit' );
        $quant_max  =   Request::getInteger( 'max' );
        $quant_min  =   Request::getInteger( 'min' );
        $period     =   Request::getInteger( 'period' );//
        $search_name=   trim(pg_escape_string( Request::getString( 'search' )));
        $sort_by    =   pg_escape_string( Request::getString( 'sortBy' ));
        $sort_reverse    =   Request::getInteger( 'sortReverse' );

        $mode = null;
        if( !isset( GroupsUtility::$special_group_ids[$group_id] ) && !is_numeric($group_id)) {
            $group_id = null;
        } elseif( $group_id == GroupsUtility::Group_Id_Special_All ) {
            $group_id = null;
        }
        $period_suffixes = array(
            '1'     =>  '',
            '7'     =>  '_week',
            '30'    =>  '_month'
        );
        $without_suffixes  = array( 'quantity' => true, 'in_search' => true );

        $search     =   array(
             '_quantityLE'  =>  $quant_max ? $quant_max : 100000000
            ,'_quantityGE'  =>  $quant_min ? $quant_min : 30000
            ,'page'         =>  round( $offset/( $limit ? $limit : 25))
            ,'pageSize'     =>  $limit
            ,'sh_in_main'   =>  true
            ,'is_page'      =>  true
        );

        //поиск по названию - глобальный
        if($search_name) {
            if( strlen( $search_name) > 5) {
                $search_name = mb_substr($search_name,0, (strlen($search_name) - 3));
            }
            $search['_nameIL'] = $search_name;
        } elseif( $group_id == GroupsUtility::Group_Id_Special_All_Not ) {
            $search['inLists'] = false;
        } elseif( $group_id ) {
            $group_entries_by_group = GroupEntryFactory::Get(array(
                'groupId'   =>  $group_id,
                'sourceType'=>  Group::STAT_GROUP,
            ));
            $entry_ids = array();
            foreach( $group_entries_by_group as $ge) {
                $entry_ids[] = $ge->entryId;
            }
            if( !empty( $group_entries_by_group )) {
                $search['_vk_public_id'] = $entry_ids;
            } else {
                 die( ObjectHelper::ToJSON(array(
                         'response' => array(
                             'list'              =>  array(),
                             'min_max'           =>  $this->get_min_max(),
                             'group_type'        =>  empty($group) ? null : $group->type
                         )
                     )
                 ));
            }
        }

        $sort_by = $sort_by ? $sort_by : 'quantity';
        if( !isset( $without_suffixes[$sort_by]))
            $sort_by .= $period_suffixes[$period];
        $sort_direction   = $sort_reverse ? ' ASC ': ' DESC ';
        $options    =   array(
            BaseFactory::OrderBy => array( array( 'name' => $sort_by, 'sort' => $sort_direction . ' NULLS LAST ' ))
        );

        $vkPublics = VkPublicFactory::Get( $search, $options );
        $diff_abs = 'diff_abs' .  $period_suffixes[$period];
        $diff_rel = 'diff_rel' .  $period_suffixes[$period];
        $visitors = 'visitors' .  $period_suffixes[$period];
        $viewers  = 'viewers'  .  $period_suffixes[$period];
        $result = array();
        foreach ($vkPublics as $vkPublic ) {
            $groups_ids = array();
            $group_entries_by_entry = GroupEntryFactory::Get( array(
                'entryId'   =>  $vkPublic->vk_public_id,
                'sourceType'=>  Group::STAT_GROUP
            ));
            foreach( $group_entries_by_entry as $ge) {
                $groups_ids[] = $ge->groupId;
            }
            $result[] =  array(
                'id'        =>  $vkPublic->vk_public_id,
                'vk_id'     =>  $vkPublic->vk_id,
                'quantity'  =>  $vkPublic->quantity,
                'name'      =>  $vkPublic->name,
                'ava'       =>  $vkPublic->ava,
                'group_id'  =>  $groups_ids,
                'admins'    =>  array(),
                'diff_abs'  =>  $vkPublic->$diff_abs,
                'diff_rel'  =>  $vkPublic->$diff_rel,
                'visitors'  =>  $vkPublic->$visitors,
                'viewers'   =>  $vkPublic->$viewers,
                'in_search' =>  $vkPublic->in_search == 't' ? 1 : 0,
                'active'    =>  $vkPublic->active== 't' ? true : false
            );
        }
        die( ObjectHelper::ToJSON(array(
                'response' => array(
                    'list'              =>  $result,
                    'min_max'           =>  $this->get_min_max(),
                    'group_type'        =>  empty($group) ? null : $group->type
                )
            )
        ));
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


    //возвращает данные о наших пабликах
    private function get_our_publics_state( $time_start, $time_stop, $groupId )
    {
        $selector = $groupId == 110 ? 2: 1;
        $publics = StatPublics::get_our_publics_list($selector);
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

    private function get_min_max()
    {
        $sql = 'SELECT MIN(quantity), MAX(quantity)  FROM ' . TABLE_STAT_PUBLICS . ' WHERE quantity > 100' ;
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