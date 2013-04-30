<?php
new stat_tables(); // Чтобы виделись константы
/**
 * @author kulikov
 * Модель. Методы получения статистики пабликов, в том числе и по группам
 */
class EntryGetter {
    public function getEntriesData() {
        list($entries, $group) = $this->getEntries();
        return array(
            'list'       =>  $entries,
            'min_max'    =>  $this->get_min_max(),
            'group_type' =>  empty($group) ? null : $group['type'],
            'groupId'    =>  empty($group) ? null : $group['groupId'],
        );
    }
    
    protected function getEntries()
    {
        $userId     =   Request::getInteger( 'userId' );
        $groupId    =   Request::getInteger( 'groupId' );
        $offset     =   Request::getInteger( 'offset' ) ?: 0;
        $limit      =   Request::getInteger( 'limit' ) ?: 25;
        $quant_max  =   Request::getInteger( 'max' ) ?: 100000000;
        $quant_min  =   Request::getInteger( 'min' ) ?: 0;
        $period     =   Request::getInteger( 'period' ) ?: 1;
        $search     =   trim(pg_escape_string( Request::getString( 'search' )));
        $sortBy     =   pg_escape_string( Request::getString( 'sortBy' ));
        $time_from  =   Request::getInteger( 'timeFrom' );
        $time_to    =   Request::getInteger( 'timeTo' ) ?: time();
        $sortReverse=   Request::getInteger( 'sortReverse' );
        $show_in_mainlist = Request::getInteger( 'show' );
 
        //"Глобальный поиск везде"
        if ( $search ) {
            $groupId = null;
        }

        $page   = ' AND publ.is_page=true ';
        $search = mb_strlen( $search ) > 5 ? mb_substr( $search, 0, mb_strlen( $search ) - 2 ) : $search;

        $group  = StatGroups::get_group( $groupId );
        //1 тип статистики
        if ( empty( $group) || $group['type'] != 2 ) {
            $allowed_sort_values = array('diff_abs', 'quantity', 'diff_rel', 'visitors', 'active', 'in_search', 'viewers' );
            $sortBy  = $sortBy && in_array( $sortBy, $allowed_sort_values, 1 )  ? $sortBy  : 'diff_abs';
            $show_in_mainlist = $show_in_mainlist && !$groupId ? ' AND sh_in_main = TRUE ' : '';

            if ( $period == 7 ) {
                if ( $sortBy == 'diff_abs' || $sortBy == 'visitors' || $sortBy == 'viewers' ) {
                    $sortBy .= '_week';
                }
                $diff_rel = 'diff_rel_week';
                $diff_abs = 'diff_abs_week';
                $visitors = 'visitors_week';
                $viewers  = 'viewers_week';
            } else if( $period == 30 ) {
                if ( $sortBy == 'diff_abs' || $sortBy == 'visitors' || $sortBy == 'viewers') {
                    $sortBy .= '_month';
                }
                $diff_rel = 'diff_rel_month';
                $diff_abs = 'diff_abs_month';
                $visitors = 'visitors_month';
                $viewers  = 'viewers_month';
            } else {
                $diff_rel = 'diff_rel';
                $diff_abs = 'diff_abs';
                $visitors = 'visitors';
                $viewers  = 'viewers';
            }

            $sortBy = $sortBy . (( $sortReverse? '' : ' DESC ') . ' NULLS LAST ');
            if ( isset( $groupId ) ) {
                $search = $search ? " AND publ.name ILIKE '%" . $search . "%' " : '';

                $sql = 'SELECT
                    publ.vk_id, publ.ava, publ.name,  publ.' . $diff_abs . ',
                    publ.' . $diff_rel . ', publ.' . $visitors . ',  publ.' . $viewers .',  publ.quantity, gprel.main_admin,
                    publ.in_search,publ.active
                FROM
                        ' . TABLE_STAT_PUBLICS . ' as publ,
                        ' . TABLE_STAT_GROUP_PUBLIC_REL . ' as gprel
                WHERE
                      publ.vk_id=gprel.public_id '
                      . $page .
                     ' AND gprel.group_id=@group_id
                      AND publ.quantity BETWEEN @min_quantity AND @max_quantity
                      AND closed is false
                      ' . $search . '
                ORDER BY '
                    . $sortBy .
              ' OFFSET '
                    . $offset .
              ' LIMIT '
                    . $limit;

                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
                $cmd->SetInteger('@group_id', $groupId);
            } else {
                $search = $search ? "AND name ILIKE '%" . $search . "%' ": '';

                $sql = 'SELECT
                            vk_id, ava, name, ' . $diff_abs . ', ' . $diff_rel . ',' . $visitors . ',' . $viewers . ', quantity,in_search,active
                        FROM '
                            . TABLE_STAT_PUBLICS . ' as publ
                        WHERE
                            quantity BETWEEN @min_quantity AND @max_quantity '
                            . $page .
                          ' AND quantity > 100'.
                            $search . $show_in_mainlist .
                      ' ORDER BY '
                            . $sortBy .
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
            $result = array();
            while ($ds->next()) {
                $row = $this->get_row( $ds, $structure );
                $admins = $this->get_admins( $row['vk_id'], isset ($row['main_admin']) ? $row['main_admin'] : ''  );
                $groups = array();
                if ( isset( $userId )) {
                    $groups = StatGroups::get_public_lists( $row['vk_id'], $userId );
                }
                $result[] =  array(
                                'id'        =>  $row['vk_id'],
                                'quantity'  =>  $row['quantity'],
                                'name'      =>  $row['name'],
                                'ava'       =>  $row['ava'],
                                'group_id'  =>  $groups,
                                'admins'    =>  $admins,
                                'diff_abs'  =>  $row[$diff_abs],
                                'diff_rel'  =>  $row[$diff_rel],
                                'visitors'  =>  $row[$visitors],
                                'viewers'   =>  $row[$viewers],
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

            $result = $this->get_our_publics_state( $time_from, $time_to, $groupId );
            $sortBy  = $sortBy && in_array( $sortBy, $allowed_sort_values, 1 )  ? $sortBy  : 'visitors';
            $a = $this->compare( $sortBy, $sortReverse );
            usort( $result, $a );
        }
        
        return array($result, $group);
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

    private function get_ava( $public_id )
    {
        $sql = 'SELECT ava
                FROM ' . TABLE_STAT_PUBLICS .
               ' WHERE vk_id=@publ_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
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

    public function get_min_max()
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

    /**
     * Получает id группы по кусочку URI
     * @param string $slug
     * @return int
     */
    public function getGroupIdBySlug($slug)
    {
        $sql = 'SELECT group_id FROM '. TABLE_STAT_GROUPS .'
        WHERE slug LIKE @slug';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetString('@slug', $slug);
        $result = $cmd->Execute();
        $result->Next();
        return $result->GetInteger('group_id');
    }

    public function updateSlugs()
    {
        $sql = 'SELECT group_id, name FROM '. TABLE_STAT_GROUPS .' ORDER BY group_id DESC';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $found = array();
        $result = $cmd->Execute();
        while ( $result->next()) {
            $name = $result->GetString('name');
            if (!isset($found[$name])) {
                $found[$name] = 1;
            } else {
                $found[$name] += 1;
            }

            $slug = self::transliterate($name);
            if ($found[$name] > 1) {
                $slug .= $found[$name] - 1;
            }

            $id = $result->GetInteger('group_id');
            $this->saveSlugForId($id, $slug);
        }
    }
    
    static public function transliterate($text) {
        preg_match_all('/./u', $text, $text);
        $text = $text[0];
        $simplePairs = array('а' => 'a', 'л' => 'l', 'у' => 'u', 'б' => 'b', 'м' => 'm', 'т' => 't', 'в' => 'v', 'н' => 'n', 'ы' => 'y', 'г' => 'g', 'о' => 'o', 'ф' => 'f', 'д' => 'd', 'п' => 'p', 'и' => 'i', 'р' => 'r', 'А' => 'A', 'Л' => 'L', 'У' => 'U', 'Б' => 'B', 'М' => 'M', 'Т' => 'T', 'В' => 'V', 'Н' => 'N', 'Ы' => 'Y', 'Г' => 'G', 'О' => 'O', 'Ф' => 'F', 'Д' => 'D', 'П' => 'P', 'И' => 'I', 'Р' => 'R',);
        $complexPairs = array('з' => 'z', 'ц' => 'c', 'к' => 'k', 'ж' => 'zh', 'ч' => 'ch', 'х' => 'h', 'е' => 'e', 'с' => 's', 'ё' => 'yo', 'э' => 'e', 'ш' => 'sh', 'й' => 'y', 'щ' => 'sh', 'ю' => 'yu', 'я' => 'ya', 'З' => 'Z', 'Ц' => 'C', 'К' => 'K', 'Ж' => 'ZH', 'Ч' => 'CH', 'Х' => 'H', 'Е' => 'E', 'С' => 'S', 'Ё' => 'YO', 'Э' => 'E', 'Ш' => 'SH', 'Й' => 'Y', 'Щ' => 'SH', 'Ю' => 'YU', 'Я' => 'YA', 'Ь' => "", 'Ъ' => "", 'ъ' => "", 'ь' => "");
        $specialSymbols = array("'" => "", "`" => "", "^" => "", " " => "_", '.' => '', ',' => '', ':' => '', '"' => '', "'" => '', '<' => '', '>' => '', '«' => '', '»' => '', ' ' => '_',);
        $translitLatSymbols = array('a', 'l', 'u', 'b', 'm', 't', 'v', 'n', 'y', 'g', 'o', 'f', 'd', 'p', 'i', 'r', 'z', 'c', 'k', 'e', 's', 'A', 'L', 'U', 'B', 'M', 'T', 'V', 'N', 'Y', 'G', 'O', 'F', 'D', 'P', 'I', 'R', 'Z', 'C', 'K', 'E', 'S',);
        $simplePairsFlip = array_flip($simplePairs);
        $complexPairsFlip = array_flip($complexPairs);
        $specialSymbolsFlip = array_flip($specialSymbols);
        $charsToTranslit = array_merge(array_keys($simplePairs), array_keys($complexPairs));
        $translitTable = array();
        foreach ($simplePairs as $key => $val)
            $translitTable[$key] = $simplePairs[$key]; 
        foreach ($complexPairs as $key => $val)
            $translitTable[$key] = $complexPairs[$key];
        foreach ($specialSymbols as $key => $val)
            $translitTable[$key] = $specialSymbols[$key]; $result = "";
        $nonTranslitArea = false;
        foreach ($text as $char) {
            if (in_array($char, array_keys($specialSymbols))) {
                $result.= $translitTable[$char];
            } elseif (in_array($char, $charsToTranslit)) {
                if ($nonTranslitArea) {
                    $result.= "";
                    $nonTranslitArea = false;
                } $result.= $translitTable[$char];
            } else {
                if (!$nonTranslitArea && in_array($char, $translitLatSymbols)) {
                    $result.= "";
                    $nonTranslitArea = true;
                } $result.= $char;
            }
        }
        return str_replace('yy', 'iy', strtolower(preg_replace("/[_]{2,}/", '_', $result)));
    }

    public function saveSlugForId($id, $slug) {
        $sql = 'UPDATE '. TABLE_STAT_GROUPS .'
            SET slug = @slug
            WHERE group_id = @group_id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetString('@slug', $slug);
        $cmd->SetInteger('@group_id', $id);
        $updateResult = $cmd->ExecuteNonQuery();
        echo $id . '  ' . $slug . '  ' . $updateResult . '<br />';
        if (!$updateResult) {
            Logger::Error('Failed to update slugs!');
        }
    }
}

?>
