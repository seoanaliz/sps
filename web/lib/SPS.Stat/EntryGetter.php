<?php
new stat_tables(); // Чтобы виделись константы
/**
 * @author kulikov
 * Модель. Методы получения статистики пабликов, в том числе и по группам
 */
class EntryGetter {
    public function getEntriesData() {
        list($entries, $groupId, $hasMore) = $this->getEntries();
        return array(
            'list'       =>  $entries,
            'groupId'    =>  $groupId,
            'hasMore'    =>  $hasMore,
            'min_max'    =>  $this->get_min_max(),
        );
    }

    static public function getUserPublics($userVkId) {
        $userFeeds = UserFeedFactory::Get(array(
            'vkId' => $userVkId,
            '_role' => array( UserFeed::ROLE_ADMINISTRATOR, UserFeed::ROLE_EDITOR, UserFeed::ROLE_OWNER,
        )));
        $targetFeedIds = array();
        foreach ($userFeeds as $userFeed) {
            $targetFeedIds []= $userFeed->targetFeedId;
        }
        if( empty( $targetFeedIds)) {
            return array();
        }
        $targetFeeds = TargetFeedFactory::Get(array('_targetFeedId' => $targetFeedIds, 'type' => TargetFeedUtility::VK ));
        $externalIds = array();
        foreach ($targetFeeds as $targetFeed) {
            $externalIds []= $targetFeed->externalId;
        }
        return $externalIds;
    }
    
    protected function getEntries()
    {
        $group_id   =   Request::getString( 'groupId' );
        $offset     =   Request::getInteger( 'offset' );
        $limit      =   Request::getInteger( 'limit' ) ?: 25;
        $quant_max  =   Request::getInteger( 'max' );
        $quant_min  =   Request::getInteger( 'min' );
        $period     =   Request::getInteger( 'period' );
        $search_name=   trim(pg_escape_string( Request::getString( 'search' )));
        $sort_by    =   pg_escape_string( Request::getString( 'sortBy' ));
        $sort_reverse    =   Request::getInteger( 'sortReverse' );
        $hasMore = false; // есть ли море?

        if (!isset(GroupsUtility::$special_group_ids[$group_id])) {
            $group_id = (int) $group_id;
        }

        $period_suffixes = array(
            '1'     =>  '',
            '7'     =>  '_week',
            '30'    =>  '_month'
        );

        if( !$sort_by ) {
            $sort_by = 'diff_abs';
        }
        if ( !$period || !in_array( $period, array_keys( $period_suffixes ))) {
            $period = '1';
        }
        $without_suffixes  = array( 'quantity' => true, 'in_search' => true );

        $search = array(
             '_quantityLE'  =>  $quant_max ? $quant_max : 100000000
            ,'_quantityGE'  =>  $quant_min ? $quant_min : 20000
            ,'limit'        =>  $limit + 1
            ,'offset'       =>  $offset
            ,'sh_in_main'   =>  true
            ,'is_page'      =>  true
            ,'active'       =>  true
        );

        if ($group_id === GroupsUtility::Group_Id_Special_My) {
            // получаем группы, которые пользователь администрирует
            $userVkId = AuthVkontakte::IsAuth();
            if ($userVkId) {
                $search['_vk_id'] = self::getUserPublics($userVkId);
            }
        } elseif ($search_name) { //поиск по названию - глобальный
            if (mb_strlen( $search_name ) > 5) {
                $search_name = mb_substr( $search_name, 0, ( mb_strlen( $search_name ) - 2 ));
            }
            $search['_nameIL'] = $search_name;
        } elseif ($group_id === GroupsUtility::Group_Id_Special_All_Not) {
            $search['inLists'] = false;
        } elseif ($group_id !== GroupsUtility::Group_Id_Special_All) {
            $group_entries_by_group = GroupEntryFactory::Get(array(
                'groupId'   =>  $group_id,
                'sourceType'=>  Group::STAT_GROUP,
            ));
            $entry_ids = array();
            foreach($group_entries_by_group as $grupEntry) {
                $entry_ids[] = $grupEntry->entryId;
            }
            if (!empty($group_entries_by_group)) {
                $search['_vk_public_id'] = $entry_ids;
            } else {
                 $result = array ();
                 goto end;
            }
        }

        if (!isset($without_suffixes[$sort_by]))
            $sort_by .= $period_suffixes[$period];
        $sort_direction   = $sort_reverse ? ' ASC ': ' DESC ';
        $options    =   array(
            BaseFactory::OrderBy => array( array( 'name' => $sort_by, 'sort' => $sort_direction . ' NULLS LAST,vk_public_id ' ))
        );
        $vkPublics = VkPublicFactory::Get($search, $options);
        if (count($vkPublics) > $limit) {
            $vkPublics = array_slice($vkPublics, 0, $limit);
            $hasMore = true;
        }
        $diff_abs = 'diff_abs' .  $period_suffixes[$period];
        $diff_rel = 'diff_rel' .  $period_suffixes[$period];
        $visitors = 'visitors' .  $period_suffixes[$period];
        $viewers  = 'viewers'  .  $period_suffixes[$period];
        $result = array();
        foreach ($vkPublics as $vkPublic) {
            $groups_ids = array();
            $group_entries_by_entry = GroupEntryFactory::Get(array(
                'entryId'   =>  $vkPublic->vk_public_id,
                'sourceType'=>  Group::STAT_GROUP
            ));
            foreach ($group_entries_by_entry as $grupEntry) {
                $groups_ids[] = $grupEntry->groupId;
            }
            $result[] =  array(
                'id'        =>  $vkPublic->vk_public_id,
                'vk_id'     =>  $vkPublic->vk_id,
                'quantity'  =>  $vkPublic->quantity,
                'name'      =>  $vkPublic->name,
                'ava'       =>  trim($vkPublic->ava),
                'group_id'  =>  $groups_ids,
                'admins'    =>  array(),
                'diff_abs'  =>  $vkPublic->$diff_abs,
                'diff_rel'  =>  $vkPublic->$diff_rel,
                'visitors'  =>  $vkPublic->$visitors,
                'viewers'   =>  $vkPublic->$viewers,
                'in_search' =>  $vkPublic->in_search == 't' ? 1 : 0,
                'active'    =>  $vkPublic->active == 't' ? true : false,
                'cpp'       =>  $vkPublic->cpp
            );
        }

        end:
        return array($result, $group_id, $hasMore);
    }

    private function get_row( $ds, $structure )
    {
        $res = array();
        foreach( $structure as $field ) {
            $res[ $field ] = $ds->getValue( $field );
        }
        return $res;
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

    public static function updateSlugs( $check_for_id = null, $show_results = true, $rename = false )
    {
        $sql = 'SELECT group_id, name, slug FROM '. TABLE_STAT_GROUPS .'  WHERE
            type = ' .GroupsUtility::Group_Global . '
            AND status != 2
            AND source = ' . Group::STAT_GROUP .'
            ORDER BY group_id DESC';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $found = array(
            '' => 1, // для переименования пустого слага в '1' в цикле ниже
            'my' => 1,
            'all' => 1,
            'not_listed' => 1
        );
        $result = $cmd->Execute();
        while ( $result->next()) {
            $name = $result->GetString('name');
            if( $rename ) {
                $slug = self::transliterate($name);
            } else  {
                $slug = $result->GetString('slug');
            }
            if (!isset($found[$slug])) {
                $found[$slug] = 1;
            } else {
                $found[$slug] += 1;
            }

            if ($found[$slug] > 1) {
                $slug .= $found[$slug] - 1;
            }

            $id = $result->GetInteger('group_id');
            if( $check_for_id && $check_for_id != $id ) {
                continue;
            }

            $updateResult = self::saveSlugForId($id, $slug);
            if( $show_results ) {
                echo "$id - $name - $slug  [$updateResult]<br />";
                if (!$updateResult) {
                    Logger::Error('Failed to update slugs!');
                }
            }
        }
    }

    static public function transliterate($rawText) {
        $text = trim(iconv('windows-1251', 'UTF-8',
            iconv('UTF-8', 'windows-1251//TRANSLIT//IGNORE', $rawText)
        ));

        $specialSpecial = array('ье' => 'ie', 'ЬЕ' => 'IE', 'Топфейс' => 'Topface', ' ' => '_'); // символы, которые заменяются группами
        foreach ($specialSpecial as $from => $to) {
            $text = str_replace($from, $to, $text);
        }

        $matches = array();
        preg_match_all('/[\-\w]/u', $text, $matches);
        $chars = $matches[0];
        $simplePairs = array('а' => 'a', 'л' => 'l', 'у' => 'u', 'б' => 'b', 'м' => 'm', 'т' => 't', 'в' => 'v', 'н' => 'n', 'ы' => 'y', 'г' => 'g', 'о' => 'o', 'ф' => 'f', 'д' => 'd', 'п' => 'p', 'и' => 'i', 'р' => 'r', 'А' => 'A', 'Л' => 'L', 'У' => 'U', 'Б' => 'B', 'М' => 'M', 'Т' => 'T', 'В' => 'V', 'Н' => 'N', 'Ы' => 'Y', 'Г' => 'G', 'О' => 'O', 'Ф' => 'F', 'Д' => 'D', 'П' => 'P', 'И' => 'I', 'Р' => 'R',);
        $complexPairs = array('з' => 'z', 'ц' => 'c', 'к' => 'k', 'ж' => 'zh', 'ч' => 'ch', 'х' => 'h', 'е' => 'e', 'с' => 's', 'ё' => 'yo', 'э' => 'e', 'ш' => 'sh', 'й' => 'y', 'щ' => 'sh', 'ю' => 'yu', 'я' => 'ya', 'З' => 'Z', 'Ц' => 'C', 'К' => 'K', 'Ж' => 'ZH', 'Ч' => 'CH', 'Х' => 'H', 'Е' => 'E', 'С' => 'S', 'Ё' => 'YO', 'Э' => 'E', 'Ш' => 'SH', 'Й' => 'Y', 'Щ' => 'SH', 'Ю' => 'YU', 'Я' => 'YA', 'Ь' => "", 'Ъ' => "", 'ъ' => "", 'ь' => "");
        $specialSymbols = array("'" => "", "`" => "", "^" => "", " " => "_", '.' => '', ',' => '', ':' => '', '"' => '', "'" => '', '<' => '', '>' => '', '«' => '', '»' => '', ' ' => '_',);
        $translitLatSymbols = array('a', 'l', 'u', 'b', 'm', 't', 'v', 'n', 'y', 'g', 'o', 'f', 'd', 'p', 'i', 'r', 'z', 'c', 'k', 'e', 's', 'A', 'L', 'U', 'B', 'M', 'T', 'V', 'N', 'Y', 'G', 'O', 'F', 'D', 'P', 'I', 'R', 'Z', 'C', 'K', 'E', 'S',);
        $charsToTranslit = array_merge(array_keys($simplePairs), array_keys($complexPairs));
        $translitTable = array();
        foreach ($complexPairs as $key => $val) {
            $translitTable[$key] = $complexPairs[$key];
        }
        foreach ($simplePairs as $key => $val) {
            $translitTable[$key] = $simplePairs[$key];
        }
        foreach ($specialSymbols as $key => $val) {
            $translitTable[$key] = $specialSymbols[$key]; $result = "";
        }
        $nonTranslitArea = false;
        foreach ($chars as $char) {
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

    public static function saveSlugForId($id, $slug) {
        $sql = 'UPDATE '. TABLE_STAT_GROUPS .'
            SET slug = @slug
            WHERE group_id = @group_id';

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst') );
        $cmd->SetString('@slug', $slug);
        $cmd->SetInteger('@group_id', $id);

        $updateResult = $cmd->ExecuteNonQuery();
        return $updateResult;
    }

}

?>
