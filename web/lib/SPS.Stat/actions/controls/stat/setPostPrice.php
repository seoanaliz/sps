<?
    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

class setPostPrice {

    private $instbarand = 52223807;
    private $smmpub = 43503600;

    /**
     * Entry Point
     */
    public function Execute()
        {
            header('Content-Type: text/html; charset=utf-8');
            set_time_limit(1000);

            $search = array(
                 '_quantityLE'  =>  100000000
                ,'_quantityGE'  =>  30000
                ,'sh_in_main'   =>  true
                ,'is_page'      =>  true
                ,'active'       =>  true
            );
            $publics = VkPublicFactory::Get($search,[BaseFactory::WithoutPages=>true, BaseFactory::WithColumns => 'vk_id']);
            $publics = ArrayHelper::Collapse($publics, 'vk_id', false);
            $publicsIds = array_keys($publics);

            $sliced_walls_array = array_chunk( $publicsIds, 25 );
            $filter = '';
            $access_token = false;
            $global_res = [];
            foreach( $sliced_walls_array as $chunk ) {
                $code = '';
                $return = "return{";
                $i = 0;
                foreach( $chunk as $public ) {
                    $id = trim( $public );
                    $code   .= 'var id' . $id . ' = API.wall.get({"owner_id":-' . $id . ',"count": 1' . $filter . ' });';
                    $return .=  "\"id$id\":id$id,";
                    $i++;
                }
                $code .= trim( $return, ',' ) . "};";
                $params = array( 'code' => $code );
                if ( $access_token ) {
                    $params['access_token'] = $access_token;
                }
                $res   = VkHelper::api_request( 'execute', $params, 0 );
                if( isset( $res->error ))
                    continue;
                foreach( $res as $id => $content ) {
                    $global_res[$id] = $content[0];
                }

            }

            arsort($a);
            $i = 0;
            $res = [];
            foreach($a as $k => $v) {
                $i++;
                $id = ltrim( $k, 'id');
                $resss[$id] = $v;
                if($i > 8) break;
            }
            $res  = VkHelper::api_request('groups.getById', ['group_ids' => array_keys($resss)]);
            foreach( $res as $public)
                $public->postsq = $resss[$public->gid];
            foreach($res as $ddfd)
                echo $ddfd->name,' http://vk.com/club', $ddfd->gid, ' постов:',$ddfd->postsq,'<br>';

            die();

            $this->changeArticleForArticleQueue(12072289, 12072322);

            die();
            if( MemcacheHelper::Flush()) {
                print_r( MemcacheHelper::Get('accessToken') );
                echo 1;
            }

            die();

        die();

            if ( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
                include 'C:\work\sps\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
                $a = new eug_stat();
                $a->Execute();
            }
            die();

            //чистим базу от старых записей
            $sql = 'SELECT * FROM "articles" where "createdAt" < now() - interval \'6 months\' and rate < 90 and "authorId" is null and "queuedAt" is null limit 100';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $structure = BaseFactory::GetObjectTree( $ds->Columns );

            while ($ds->Next()) {
                /** @var $object Comment */
                $object = BaseFactory::getObject($ds, ArticleFactory::$mapping, $structure);
                $result[$object->articleId]= $object;
            }
            $check = ArticleQueueFactory::Get(['_articleId' => array_keys($result)]);
            if( $check) {
                foreach( $check as $aq) {
                    unset($result[$aq->articleId]);
                }
            }

            $records = ArticleRecordFactory::Get(['_articleId' => array_keys($result)]);
            print_r([$result, $records]);
            foreach( $records as $record) {

                ArticleRecordFactory::PhysicalDelete($record);
            }
             foreach( array_keys($result) as $id) {
                ArticleFactory::PhysicalDelete($result[$id]);
            }

            die();

            if ( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
                include 'C:\work\sps\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
                $a = new eug_stat();
                $a->Execute();
            }
            die();

            $botsForPromote = [
                227533173,
                
            ];

            $publics = [
                36959676,
                35806721,
                35807148,
                36959733,
                35807199,
                35806476,
                36959959,
                36621543,
                35807284,
                37140953,
                38000303,
                35807044,
                38000455,
                36621560,
                35807216,
                37140910,
                36959798,
                37140977,
                36959483,
                35806378,
                35807213,
                38000361,
                36621513,
                35806186,
                38000467,
                38000487,
                35807190,
                38000341,
                38000435,
                43503681,
                43503725,
                35807071,
                43503694,
                35807273,
                38000323,
                38000382,
                38000393,
                43503630,
                38000555,
                43503753,
                43157718,
                43503575,
                43503503,
                43503550,
                43503460,
                43503264,
                43503298,
                43503235,
                43503431,
                43503315,
                52223807,
            ];
            $cookies = 'remixdt=0; remixmid=; remixsid6=; remixgid=; remixemail=; remixpass=; remixpermit=; remixsslsid=; remixlang=3; remixrec_sid=; remixmdevice=1920/1080/1/!!-!!!!; remixoldmail=; remixreg_sid=; remixapi_sid=; remixsid=e7c500315fa6b3b0e2dfc695d64d972a0fecd9931b9864cb6e7a5; remixseenads=0; audio_vol=29; remixrefkey=ae9a31b02000e1e0b7; remixflash=11.8.800; remixscreen_depth=32';
//                $hash = $this->getHashForPromote('27421965','187850505',$cookies);
//                print_r($hash);
//                $this->promote(27421965,187850505,$cookies, $hash);

        //повысить пачку юзеров по пачке пабликов
        foreach( $botsForPromote as $botId) {
            foreach( $publics as $publicId ) {
                $hash = $this->getHashForPromote($publicId,$botId,$cookies);
                echo $hash, '<br>';
                sleep(0.5);
                $this->promote($publicId,$botId,$cookies, $hash);
                sleep(0.5);
                echo "повысил $botId в vk.com/club$publicId <br>";
            }
        }
        die();


        $res = explode('<!>', $res);


        die();



        $bots_loading = array();


        //проверить соответствие наличия паблика в глобальной группе и его записи об этом
        $publics = VkPublicFactory::Get(array("inLists" => true), array(BaseFactory::WithoutPages => true));
        $groups  = GroupFactory::Get();
        $forUpdate = array();
        foreach( $publics as $vkPublic) {
            $rels = GroupEntryFactory::Get(array(
                'entryId'   =>  $vkPublic->vk_public_id,
                'sourceType'=>  Group::STAT_GROUP
            ));

            $inLists = false;
            foreach ($rels as $ge) {
                if (isset( $groups[$ge->groupId]) && $groups[$ge->groupId]->type == GroupsUtility::Group_Global) {
                    if( $vkPublic->vk_id == '32015300') {
                        echo 'here';
                        die();
                    }
                    $inLists = true;
                    break;
                }
            }
            if( $inLists <> $vkPublic->inLists) {
                $vkPublic->inLists = $inLists;
                $forUpdate[] = $vkPublic;
            }
        }

        VkPublicFactory::UpdateRange($forUpdate);
        die();

        //прогоняем паблики на предмет ботов

        $feeds = TargetFeedFactory::Get(array('isOur'=>true, 'type' => 'vk' ));
        echo count( $feeds) . '<br>';
        foreach ( $feeds as $feed ) {
            echo '<br><br> Работаем с пабликом ' . $feed->externalId . '(' .$feed->targetFeedId.')<br>' ;
            $userFeeds  =  UserFeedFactory::Get(array( 'targetFeedId' => $feed->targetFeedId ));
            $userFeeds  =  ArrayHelper::Collapse( $userFeeds, 'vkId', $convertToArray = false);
            $vkIds = array_keys($userFeeds);
            if (empty($vkIds)) {
                echo 'пустые vkId для tf ' .$feed->targetFeedId . ' <br>';
                continue;
            }

            $botAuthors =  AuthorFactory::Get(
                array(
                    'vkIdIn' => $vkIds,
                    'isBot' =>  true
                ));

            if (empty($botAuthors)) {
                echo 'пустые $botAuthors для tf ' .$feed->targetFeedId . ' <br>';
                continue;
            }
            $botAuthors =  ArrayHelper::Collapse( $botAuthors, 'vkId', $convertToArray = false);
            $tokens     =  AccessTokenFactory::Get(array(
                'vkIdIn'  => array_keys( $botAuthors ),
                'version' => AuthVkontakte::$Version
            ));

            echo 'всего ботов: ' . count( $tokens ) . '<br>';
            foreach ( $tokens as $token ) {
                echo '<a href="http://vk.com/id' . $token->vkId . '">' . $token->vkId . '</a><br>';
                $params = array(
                    'group_ids'     =>   $feed->externalId,
                    'access_token'  =>   $token->accessToken
                );

                $res = VkHelper::api_request('groups.getById', $params, 0 );
                sleep(0.4);
                if( isset( $res->error)) {
                    echo 'ломанный токен ' . $token->accessToken . ' http://vk.com/id' . $token->vkId . '<br>';
                    continue;
                }
                if( !isset( $res[0]->is_admin ) or !$res[0]->is_admin ){
                    echo 'ломанный токен ' . $token->accessToken . ' http://vk.com/id' . $token->vkId . '<br>';
                }
                if( !isset( $bots_loading[$token->vkId] )) {
                    $bots_loading[$token->vkId] =0;
                }
                $bots_loading[$token->vkId]++;
            }
        }
        print_r($bots_loading);
        echo 'end';
        die();

        $groupUserArray =  GroupUserFactory::Get( array(
            'vkId'          =>  670456,
            'sourceType'    =>  Group::STAT_GROUP
        ), array(
            'orderBy' => 'place nulls Last'
        ));

        $i = 0;
        foreach($groupUserArray as $gu) {
            $gu->place = ++$i;
        }

        $old_place = 7;
        $new_place = 5;
        $groupUserArray = ArrayHelper::Collapse($groupUserArray, 'place', false );
        $moved = $groupUserArray[$old_place];
        if ( $old_place - $new_place > 0 ) {

            for ( $i = $new_place; $i <= $old_place; $i++ ) {
                $moved_tmp = $groupUserArray[$i];
                $groupUserArray[$i] = $moved;
                $groupUserArray[$i]->place = $i;
                $moved = $moved_tmp;
            }
        } else {
            for ( $i = $old_place; $i < $new_place; $i++ ) {
                $groupUserArray[$i] = $groupUserArray[$i + 1];
                $groupUserArray[$i]->place = $i;
            }

            $groupUserArray[$new_place] = $moved;
            $groupUserArray[$i]->place = $new_place;
        }
        print_r($groupUserArray);

        die();

//        include_once 'C:\wrk\sps\web\lib\SPS.Site\actions\daemons\SyncFbSources.php';
//        $a = new SyncFbSources();
//        $a->Execute();
//        die();


        preg_match('/ic_dstc-hd\"><\/i>(.+?)</', $a, $matches);
        print_r($matches);

        die();
        //шаг первый - мигратим группы
        $old_new_groups = array();

        $sql = 'select * from stat_groups';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $ds  = $cmd->Execute();
        $counter = 0;
        while( $ds->Next()) {
            $group       = new Group();
            $group->name = $ds->GetValue('name');
            $group->source = Group::STAT_GROUP;
            $group->status = $ds->GetInteger('group_id');
            $group->type   = $ds->GetBoolean('general') ? GroupsUtility::Group_Global : GroupsUtility::Group_Private;
            if( $ds->GetValue( 'type' ) == 2)
                $group->type = GroupsUtility::Group_Shared_Special;
            $group->general= $ds->GetBoolean('general');
            GroupFactory::Add($group, array(BaseFactory::WithReturningKeys=>true));
            $old_new_groups[$ds->GetInteger('group_id')] = $group->group_id;
            $counter ++;
        }
        echo '<br><br>';
        echo 'Создал ' . $counter . ' group';

        echo '<br><br>';
        //шаг второй, мигратим stat_g_p_r
        $sql = 'select * from stat_group_public_relation';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $groups = array();
        $counter = 0;
        while( $ds->Next()) {

            $group_id   = $ds->GetInteger('group_id');
            $vk_id      = $ds->GetInteger('public_id');
            if(!isset( $old_new_groups[$group_id])) {
                echo 'пропустили stat_group_public_relation <br>';
                continue;
            }
            $tmp_p      = VkPublicFactory::GetOne(array('vk_id' => $vk_id));
            if( empty($tmp_p )) {
                echo 'пропустили stat_group_public_relation <br>';
                continue;
            }
            $groupPublic = new GroupEntry( $old_new_groups[$group_id], $tmp_p->vk_public_id, 3);
            GroupEntryFactory::Add($groupPublic);
            $counter ++;
        }
        echo '<br><br>';
        echo 'Создал ' . $counter . ' stat_group_public_relation';

        echo '<br><br>';

        //шаг 3, мигратим stat_g_u_r
        $counter = 0;
        $sql = 'select * from stat_group_user_relation';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $users = array();
        while( $ds->Next()) {
            $user_id = $ds->GetInteger('user_id');
            $group_id = $ds->GetInteger('group_id');
            if(!isset( $old_new_groups[$group_id])) {
                echo 'пропустили stat_group_user_relation <br>';
                continue;
            }

            $groupUser = new GroupUser($old_new_groups[$group_id], $user_id, 3 );
            GroupUserFactory::Add($groupUser);
            $counter ++;
        }
        echo '<br><br>';
        echo 'Создал ' . $counter . ' stat_group_user_relation';

        echo '<br><br>';

        die();

        echo ObjectHelper::ToJSON(array( 'response' => true ));
    }

    //оно работает!
    public function changeArticleForArticleQueue( $oldArticleId, $newArticleId) {
        $sql = ' select * from "articleQueues" where  "articleId" = 12072289 and extract(dow from "startDate")::int in (4) and "statusId" = 1';
        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $ds = $cmd->execute();
//            $aq->articleId = 11989820;

        $structure = BaseFactory::getObjectTree($ds->Columns);
        $article = ArticleFactory::GetById($newArticleId);
        $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $article->articleId));

        $ar = clone( $articleRecord);
        $ar->articleRecordId = null;
        $ar->articleId = null;
        while( $ds->Next()) {
            $aq = BaseFactory::GetObject($ds, ArticleQueueFactory::$mapping, $structure);
            ArticleQueueFactory::Delete($aq);
            $aq->articleId = $newArticleId;
            $aq->articleQueueId = null;
            $aq->statusId = 1;
            ConnectionFactory::BeginTransaction();

            $sqlResult = ArticleQueueFactory::Add($aq, array(BaseFactory::WithReturningKeys => true));
            if ($sqlResult) {
                $ar->articleQueueId = $aq->articleQueueId;

                $sqlResult = ArticleRecordFactory::Add($ar, array(BaseFactory::WithReturningKeys => true));
                print_R($ar);
            }

            ConnectionFactory::CommitTransaction($sqlResult);
        }

    }

    public function getHashForPromote( $public_id, $adminId, $cookie) {
        $url = "http://vk.com/groupsedit.php?act=edit_admin&addr=$adminId&al=1&id=$public_id";

        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT , 180 );
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "<br>error in curl: ". curl_error($ch) ."<br>";
            return 'error in curl: '. curl_error($ch);
        }

        curl_close($ch);
        preg_match( "/pbind\(.*?,.?'(.*?)'\)\);/", $result, $hash);
        return $hash[1];
    }

    public function promote( $public_id, $adminId, $cookie, $hash ) {
        $params = array(
            'act'       =>  'done_admin',
            'addr'      =>  $adminId,//юзер, которого промоутим
            'contact'   =>  null,
            'email'     =>  null,
            'phone'     =>  null,
            'position'  =>  null,
            'hash'      =>  $hash,
            'al'        =>  1,
            'level'     =>  2,
            'id'        =>  $public_id, //id паблика
        );
        $url = 'http://vk.com/groupsedit.php?' . http_build_query($params);

        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT , 180 );
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "<br>error in curl: ". curl_error($ch) ."<br>";
            return 'error in curl: '. curl_error($ch);
        }

        curl_close($ch);
//        print_r($result);
    }

    public function getReposts() {
        $token  = '5b5aa1b8947b854d7d3eebc53210b5054eca35197c13ff6096698e766d19558a5e1425876c0da840ec837';
        $params = array(
            'owner_id'      =>  -38000404,
            'post_id'       =>  89,
            'access_token'  =>  $token,
            'v'             =>  '3.0',
            'count'         =>  1000,
            'offset'        =>  0
        );
        $weGotAWinner = array();
        while( true ) {
            $res = VkHelper::api_request('wall.getReposts', $params);

            foreach( $res->items as $item ) {
//                if ( strpos( $item->from_id, '-' ) !== false)
//                    continue;

                $weGotAWinner[] = $item->from_id;
            }
            sleep(0.9);

            if ( (int) count($res->items) < 950 ) {
                break;
            }
            $params['offset'] += 1000;
        }
        foreach( $weGotAWinner as $k => $v ) {

            if ( strpos($v, '-' ) !== false)
                $full_adress = 'http://vk.com/public' . ltrim( $v,'-');
            else
                $full_adress = 'http://vk.com/id' . $v;
            echo $k . ' | ' . $full_adress . '<br>';
        }
//        print_r($weGotAWinner);
        return($weGotAWinner);
    }

    public function getComments() {
        $token  = '5b5aa1b8947b854d7d3eebc53210b5054eca35197c13ff6096698e766d19558a5e1425876c0da840ec837';
        $params = array(
            'group_id'      =>  38000404,
            'topic_id'      =>  28673182,
            'need_likes'    =>  1,
            'access_token'  =>  $token,
            'v'             =>  '5.0',
            'count'         =>  100,
            'offset'        =>  0
        );
        $weGotAWinner = array();
        while( true ) {
            $res = VkHelper::api_request('board.getComments', $params);
            foreach( $res->items as $item ) {
                if( $item->id  < 375 )
                    continue;

                if ( strpos( $item->from_id, '-' ) !== false)
                    continue;
                preg_replace('/(\[.*?\|.*?\],?/)','', $item->text);
                $row = explode('
', $item->text);
                if(count($row) == 1) {
                    $row = explode(',', $item->text);
                }
                $row = array_filter ($row );
                if ( !isset($weGotAWinner[$item->from_id]))
                    $weGotAWinner[$item->from_id] = $row;
                else {
                    $weGotAWinner[$item->from_id] = array_merge($weGotAWinner[$item->from_id],$row);
                }
            }
            sleep(2);

            if (  count($res->items) < 100 ) {
//                print_r($res);
                break;
            }
            $params['offset'] += 100;
        }
        foreach( $weGotAWinner as $k => $v ) {
            foreach($v as $vars) {
                echo $k .'%' . $vars . '<br>';
            }
        }
        print_r($weGotAWinner);
    }

    public function getRepostsHardcore($owner_id, $post_id) {
        //ручками получаем html листа репостов, тырк его в файл и понеслась
        $a = file_get_contents('c:/work/123.txt');
        preg_match_all( '/div id="post(-?\d{1,12})_\d{1,12}" class="post/', $a, $matches);
//        print_r($matches);
        $i = 1;
        $a = [];
        foreach( $matches[1] as $id) {
            $id = reset(explode('_', $id));
            if ( strpos($id, '-' ) !== false)
                $full_adress = 'http://vk.com/public' . ltrim( $id,'-');
            else
                $full_adress = 'http://vk.com/id' . $id;
            echo $i . ' | ' . $full_adress . '<br>';
            $i++;
        }
        return $matches[1];

//               file_put_contents('c:/work/wkview_response.txt', $res);
    }


    //for instabrand
    public function get_barter_stat_by_public(  )
    {
        $from       =  $_REQUEST['from'];
        $type       =  $_REQUEST['type'];
        if( !$type )
            $type = 'new';
        $public_id  =  $_REQUEST['public_id'];
        if(!$public_id)
            $public_id = $this->instbrand;
        $authors_array = array(
            75849904   => array(),
            7875269    => array(),
            83475534   => array(),
            199955522  => array(),
            135339094  => array(),
            14412297   => array(),
            161113216  => array(),
            130852478  => array(),
            1715958    => array(),
        );
        $rows_array = array('head'=>'');
        $dates_array = array();
        $from = new DateTime( $from );
        while ( time() > $from->format('U')) {
            $string_date = $from->format('d-m-Y');
            $dates_array[] = $string_date;
            $sql = 'SELECT creator_id, sum(neater_subscribers), sum(end_subscribers - start_subscribers) as old_way
                        FROM barter_events
                        WHERE status IN (4,6) AND detected_at::date = @FROM AND target_public = @target_public
                        GROUP BY creator_id
                        ORDER BY creator_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
            $cmd->SetDate( '@FROM', $from );
            $cmd->SetString( '@target_public', $public_id );
            $ds = $cmd->Execute();
            $res = array();
            while( $ds->Next()) {
                //пропускаем посты от наших
                $author_id = $ds->GetValue('creator_id');
                if( in_array($author_id, StatUsers::$editors_black_list))
                    continue;
                $res[$author_id] = array('old_way' => $ds->GetInteger('old_way'),
                    'new_way' => $ds->GetInteger('sum'));
            }
            unset( $author_id);
            foreach( $res as $author_id => $author_row ) {
                $authors_array[$author_id][$string_date] = $author_row;
            }

            unset($res);
            $from->modify( '+1 day' );
        }
        $authors = array_keys( $authors_array );
        unset( $author_id);
        foreach( $authors as $author_id ) {
            $rows_array[$author_id] = $author_id . ' | ';
            foreach( $dates_array as $date ) {
                if( !isset( $authors_array[$author_id][$date])) {
                    $rows_array[$author_id]  .=  ' 0 | ';
                } else {
                    $rows_array[$author_id]  .=  $authors_array[$author_id][$date][$type . '_way'] . ' | ';
                }
            }
        }
        $rows_array['head'] = '|' . implode( ' | ', $dates_array );
        print_r($rows_array);
        die();

    }

    //for smmpub
    public function get_barter_activity( )
    {
        $from       =  $_REQUEST['from'];
        $public_id  =  $_REQUEST['public_id'];
        if(!$public_id)
            $public_id = $this->smmpub;
        $from       = new DateTimeWrapper( $from );
        $a = BarterEventFactory::Get(array( '_status' => array(4,6), 'target_public'=> $public_id, '_start_search_atGE' => $from ), array( 'orderBy' => ' posted_at ' ));
        echo 'количество | где публикуем | кто публикует | когда | провисел до перекрытия, мин  ' . '<br>';
        foreach( $a as $event ) {
            $sql = 'select quantity from stat_publics_50k where vk_id=@vk_id';
            $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
            $cmd->SetInteger('@vk_id', $event->barter_public);
            $ds = $cmd->Execute();
            $q = 0;
            if( $ds->Next())
                $q = $ds->GetInteger('quantity');
            $lifetime = 0;
            if( $event->barter_overlaps ) {
                $lifetime = ( end( explode(',', $event->barter_overlaps)) - $event->posted_at->format('U')) / 60;
            }
            echo $q . ' | ' . $event->barter_public . ' | ' . $event->creator_id . ' | ' . $event->posted_at->format('d-m-Y H:i') . ' | ' . round($lifetime) . '<br>';
        }
    }

    public function check_news()
    {
        $text = "vascular disease";
        $start_search_from = DateTimeWrapper::Now()->modify('- 1 month')->format('U');

        $params = array(
            'q'             =>  $text,
            'count'         =>  100,
            'start_time'    =>  $start_search_from
        );
        $res = VkHelper::api_request( 'newsfeed.search', $params );
        print_r($res);
    }

    public function making_money()
    {

    }

    public function excel_stat()
    {
        include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
        $a = new eug_stat();
        $a->Execute();
        die();
    }

    public function half_life()
    {
        $response = 'Смотри в логи! ';
        if( isset( $_REQUEST['set'])  && isset( $_REQUEST['gid']) && is_numeric($_REQUEST['gid'])) {
            $hf = hf::fill_hf( $_REQUEST['gid']);
            if ( !empty( $hf )) {
                $response = 'Ну надо же...';
            }
            die( $response );
        } elseif( isset( $_REQUEST['update'] )) {
            $hfs = hf_factory::get(array());
            foreach( $hfs as $hf ) {
                $users_remain = $hf->get_changes();
            }
        }
    }


    public function get_public_stats_page( $public_id, $time_from = 0, $time_to = 0 )
    {
        $page = file_get_contents( 'http://vk.com/stats?gid=' . $public_id );
        file_put_contents( '1.txt', $page );
    }

    public function connect($link,$cookie=null,$post=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        if($cookie !== null)
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        if($post !== null)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $otvet = curl_exec($ch);
        curl_close($ch);
        return $otvet;
    }

    #стата - виджет
    private $samorost_publics_array = array(

        36959676
    ,35806721
    ,35807148
    ,36959733
    ,35807199
    ,35806476
    ,36959959
    ,36621543
    ,35807284
    ,37140953
    ,38000303
    ,35807044
    ,38000455
    ,36621560
    ,35807216
    ,37140910
    ,36959798
    ,37140977
    ,36959483
    ,35806378
    ,35807213
    ,38000361
    ,36621513
    ,35806186
    ,38000467
    ,38000487
    ,35807190
    ,38000341
    ,38000435
    ,43503681
    ,43503725
    ,35807071
    ,43503694
    ,35807273
    ,38000323
    ,38000382
    ,38000393
    ,43503630
    ,38000555
    ,43503753
    ,43157718
    ,43503575
    ,43503503
    ,43503550
    ,43503460
    ,43503264
    ,43503298
    ,43503235
    ,43503431
    ,43503315
    );
    #стата - виджет
    private $samorost = array(
        400,
        250,
        450,
        500,
        200,
        150,
        300,
        350,
        550,
        450,
        300,
        350,
        150,
        150,
        100,
        200,
        150,
        200,
        80,
        500,
        150,
        150,
        250,
        250,
        150,
        60,
        200,
        200,
        150,
        100,
        70,
        80,
        120,
        80,
        100,
        70,
        50,
        60,
        200,
        100,
        200,
        100,
        100,
        100,
        100,
        70,
        50,
        40,
        40,
        40
    );
    #стата - виджет
    public function create_excel_list( $data_array, $from = 0, $to = 10000000000000)
    {

        include_once 'C:/wrk/classes/PHPExcel.php';
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Первый лист');

        for( $i = 2; $i < 33; $i ++) {
            $cell = $aSheet->getCellByColumnAndRow( $i, 1);
            $cell->setValue( $i-1 );
        }

        $row = 2;
        $samorost = current( $this->samorost);

        $total_views = 0;
        $total_subs_coverage = 0;
        $total_full_coverage = 0;


//            echo '<br>';
//            echo  'full coverage = ' . $total_full_coverage;
//            echo  'subs coverage = ' . $total_subs_coverage;

//отдаем пользователю в браузер
        include("C:/wrk/classes/PHPExcel/Writer/Excel5.php");
        $filename = 'dwl_dfdf';
        $localPath  = Site::GetRealPath('temp://' . $filename . '.xls');
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save($localPath);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="rate.xls"');
        header ("Content-Length: " . filesize( $localPath ));
        header ("Content-Disposition: attachment; filename=" . $localPath);
        header('Cache-Control: max-age=0');
        readfile( $localPath );
//            file_put_contents('c:/wrk/1.xls', '');


    }
    #стата - виджет, entry point
    public function get_public_stats_wo_api()
    {
        $mail = "akalie@list.ru";
        $pass = "7n@tion@rmy";

        $otvet=VkHelper::connect("http://login.vk.com/?act=login&email=$mail&pass=$pass");
        if(!preg_match("/hash=([a-z0-9]{1,32})/",$otvet, $hash )) {
            die("Login incorrect");
        }
        $otvet=VkHelper::connect("http://vk.com/login.php?act=slogin&hash=" . $hash[1] );
        preg_match( "/remixsid=(.*?);/", $otvet, $sid );
        $cookie = "remixchk=5; remixsid=$sid[1]";

        $res = array();
//            $fct = 0;
//            $ict = 0;
        $total_vidgets_members = 0;
        foreach( $this->samorost_publics_array as $public_id ) {
            $res[$public_id] = array();
            $page = VkHelper::connect( 'http://vk.com/stats?gid=' . $public_id, $cookie );
//                $page = VkHelper::connect( 'http://vk.com/stats?act=reach&gid=' . $public_id, $cookie );
            file_put_contents( '1.txt', $page );

            $page = file_get_contents('1.txt');

            preg_match( '/Total members",(.*?\:\[\[.*?]])/', $page, $tot_members );
            preg_match( '/f":1,"name":"New members".*?("d"\:\[\[.*?]])/', $page, $members_growth );
//                preg_match( '/"Members lost",(.*?\:\[\[.*?]])/', $page, $members_loss );
            preg_match( '/{"name":"New members","l".*?,("d".*?]])/', $page, $vidget_members );
            preg_match( '/unique visitors.*?,("d".*?]])/', $page, $unique_visitors );
            preg_match( '/Pageviews.*?,("d".*?]])/', $page, $views );
            preg_match( '/Full coverage.*?,("d".*?]])/', $page, $full_coverage );
            preg_match( '/Followers coverage.*?,("d".*?]])/', $page, $followers_coverage );


            preg_match( '/Pageviews.*?,("d".*?]])/', $page, $views );
//                $full_coverage  = json_decode( '{' . $full_coverage[1] .  '}' )->d;
//                $fct += $full_coverage[1][1];
//                $followers_coverage  = json_decode( '{' . $followers_coverage[1] . '}' )->d;
//                $ict += $followers_coverage[1][1];

            $views           = json_decode( '{' . $views[1] .           '}' )->d;
            $tot_members     = json_decode( '{' . $tot_members[1] .     '}' )->d;
//                $members_loss    = json_decode( '{' . $members_loss[1] .    '}' )->d;
            $vidget_members  = json_decode( '{' . $vidget_members[1] .  '}' )->d;
            $members_growth  = json_decode( '{' . $members_growth[1] .  '}' )->d;
            $unique_visitors = json_decode( '{' . $unique_visitors[1] . '}' )->d;
//                $full_coverage = json_decode( '{' . $full_coverage[1] . '}' )->d;
//                $followers_coverage = json_decode( '{' . $followers_coverage[1] . '}' )->d;


            $res[$public_id] = $this->key_maker( 1 , 1, 1, 1, 1, $full_coverage, $followers_coverage );
            $res[$public_id] = $this->key_maker( $tot_members , $vidget_members, $unique_visitors, $views, $members_growth   );

        }


        $this->create_excel_list( $res, 1359677096, 1362096649 );
    }
    #стата - виджет
    public function key_maker( $total_members, $vidget_members, $unique_visitors, $views, $members_growth, $full_coverage=array(), $followers_coverage = array())
    {
        $count = !empty( $full_coverage) ? count( $full_coverage)  : count( $total_members );
        $res = array();
        for( $i = 0; $i < $count; $i++ ) {
            $date = !empty( $full_coverage) ?  $full_coverage[$i][0] : $total_members[$i][0];

            if( !empty( $full_coverage )) {
                $res[$date]['full_coverage']       = isset( $full_coverage[$i][1] ) ? $full_coverage[$i][1] : 0;
                $res[$date]['followers_coverage']  = isset( $followers_coverage[$i][1] ) ? $followers_coverage[$i][1] : 0;
            } else {
                $res[$date]['views']            = isset( $views[$i][1] ) ? $views[$i][1] : 0;
                $res[$date]['total_members']    = $total_members[$i][1];
                $res[$date]['members_growth']   = $members_growth[$i][1] ;
                $res[$date]['vidget_members']   = isset( $vidget_members[$i][1]  ) ? $vidget_members[$i][1]  : 0;
                $res[$date]['unique_visitors']  = isset( $unique_visitors[$i][1] ) ? $unique_visitors[$i][1] : 0;
            }
        }
        return $res;
    }

    public function get_authors_stats_blabla( )
    {
        set_time_limit(0);
        $conn = ConnectionFactory::Get('tst');
        //получаем список авторов, сортировка по количеству ведомых пабликов
        $sql = 'select vk_id, count(*) from stat_admins group by vk_id order by count desc;';
        $cmd = new SqlCommand( $sql, $conn );
        $ds = $cmd->Execute();
        $admins = array();
        while( $ds->Next()) {
            if($ds->GetInteger('count') < 3)
                continue;
            $admins[$ds->GetInteger( 'vk_id')] = array('count'=> $ds->GetInteger('count'));
        }

        //наполняем контентом: имя, ссыль, список пабликов(ссылей), сумма подписчиков

        foreach( $admins as $admin_id=>&$data ) {
            $sql = 'select role, ad.name, quantity,publ_id from stat_admins as ad join stat_publics_50k as st on publ_id=st.vk_id where ad.vk_id = @vk_id';
            $cmd = new SqlCommand( $sql, $conn );
            $cmd->SetInteger('@vk_id', $admin_id );
            $ds = $cmd->Execute();
            $data['quantity'] = 0;
            while( $ds->Next()) {
                $data['role'][] = $ds->GetValue('role');
                $data['public_id'][] = 'http://vk.com/club' . $ds->GetValue('publ_id');
                if( !isset( $temp_data['name'] ))
                    $data['name'] = $ds->GetValue('name');
                $data['quantity'] += $ds->GetInteger( 'quantity' );
            }
            $data['role'] = implode( ', ', array_unique( $data['role']));
            $data['public_id'] = implode( ', ', $data['public_id']);
            $sql = 'select 1 from stat_users user_id = @vk_id';
            $cmd = new SqlCommand( $sql, $conn );
            $cmd->SetInteger('@vk_id', $admin_id );
            $ds = $cmd->Execute();
            $data['is_in_stat'] = $ds->Next();
        }


        include_once 'C:/wrk/classes/PHPExcel.php';
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Первый лист');

        $row = 1;
        echo '<table style="border: 4px double black; border-collapse: collapse;">';
        echo '<tbody>';
        foreach( $admins as  $admin_id=>$data ) {
            $column = 0;
            $row++;
            echo '<tr>';
//                $cell = $aSheet->getCellByColumnAndRow( $column++, $row );
            echo '<td>http://vk.com/id' , $admin_id, '</td>';
            //                $cell = $aSheet->getCellByColumnAndRow( $column++, $row );
            echo '<td>' , $data['name'], '</td>';
            echo '<td>' , $data['count'], '</td>';
//                $cell = $aSheet->getCellByColumnAndRow( $column++, $row );
            echo '<td>' , $data['quantity'], '</td>';
//                $cell = $aSheet->getCellByColumnAndRow( $column++, $row );
            echo '<td>' , $data['role'] , '</td>';
//                $cell = $aSheet->getCellByColumnAndRow( $column++, $row );
            echo '<td>' ,  $data['public_id'] , '</td>';
//                $cell = $aSheet->getCellByColumnAndRow( $column++, $row );
//                $cell = $aSheet->getCellByColumnAndRow( $column, $row );
            echo '<td>' , ( $data['is_in_stat'] ? 'есть!!' : 'nope'), '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
//
//            include("C:/wrk/classes/PHPExcel/Writer/Excel5.php");
//            $objWriter = new PHPExcel_Writer_Excel5($pExcel);
//            file_put_contents('c:/wrk/1.xls', '');
//            $objWriter->save('c:/wrk/1.xls');
    }

    public function libxml_display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in <b>$error->file</b>";
        }
        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    public function libxml_display_errors() {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            print libxml_display_error($error);
        }
        libxml_clear_errors();
    }

// Enable user error handling



    public static function delete_non_friends( $user_id )
    {
        $access_token = StatUsers::get_access_token( $user_id );
        $params = array(
            'access_token'    =>    $access_token,
            'count'           =>    1000
        );
        $followers = VkHelper::api_request('subscriptions.getFollowers', $params );
        sleep(0.5);
        $params = array(
            'access_token'    =>   $access_token,
            'count'           =>   1000,
            'offset'          =>   1000
        );
        $followers2 = VkHelper::api_request('subscriptions.getFollowers', $params );

        $followers  = array_merge( $followers->users, $followers2->users );

        $res = array();
        $sql = 'select rec_id from ' . TABLE_MES_DIALOGS. ' WHERE user_id = 13049517';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
        $ds = $cmd->Execute();
        while( $ds->Next() ) {
            $res[] = $ds->GetInteger(  'rec_id');
        }
        $result = array_intersect( $followers, $res );
        print_r( count( $result ));
        echo '<br><br><br><br>';
        $uids = implode( ',', $result );
        sleep(0.4);
        $params = array(
            'uids'          =>  $uids,
            'access_token'  =>  $access_token,
        );
        $res = VkHelper::api_request( 'friends.areFriends', $params );

        $friends = array();
        foreach( $res as $fgf ) {
            echo  $fgf->friend_status . '<br>';
            if ( $fgf->friend_status == 3 ) {

                continue;
            }

            $friends[] = $fgf->uid;
        }
        print_r( count( $friends ));

        foreach( $result as $rec_id ) {
            $dialog_id = MesDialogs::get_dialog_id( $user_id, $rec_id );
            $sql = 'delete from ' . TABLE_MES_DIALOGS . ' where user_id=@user_id and rec_id=@rec_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@rec_id', $rec_id );
            $cmd->SetInteger( '@user_id', $user_id );
            $cmd->Execute();
            echo $cmd->GetQuery() . '<br>';
            $sql = 'delete from ' . TABLE_MES_GROUP_DIALOG_REL . ' where dialog_id=@dialog_id';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
            $cmd->SetInteger( '@dialog_id', $dialog_id );
            $cmd->Execute();
            echo $cmd->GetQuery() . '<br>';
        }
    }

    public function get_gender_likes( $public_id, $post_id, $access_token = 0 )
    {
        $params = array( 'owner_id'  =>  '-' . $public_id,
            'post_id'   =>  $post_id,
            'count'     =>  1000
        );
        print_r( $this->vk_execute( 'wall.getLikes', $params, 1000, 'user->uid' ));
        die();
        if ( $access_token )
            $params['access_token'] = $access_token;
        while (1) {
            $res = VkHelper::api_request( 'wall.getLikes', $params );
        }
    }

    //оболочка для execute
    //$method - метод VK API
    //$params - список параметров для метода(offset не нужен)
    //$offset_step - шаг оффсета
    //$get_param - название параметра, который нужно получить(напр. для group.getMembers - users)
    public function vk_execute( $method, $params, $offset_step, $get_param, $save_func = '' )
    {
        $params['offset'] = 0;
        $result = array();
        while (1) {
            $values = '';
            $code = '';
            $return = "return{";
            for ( $i = 0; $i < 25; $i++ ) {
                $query_line = '';
                foreach( $params as $parameter => $value ) {
                    $query_line .= '"' . $parameter . '":' . $value . ',';
                }

                $query_line = '({' . trim( $query_line, ',' ) . '})';
                $code   .= "var a$i = API.$method$query_line;";
                $return .= "\"a$i\":a$i,";
                $params['offset'] += $offset_step;
            }

            $code .= trim( $return, ',' ) . "};";
            $res = VkHelper::api_request( 'execute', array( 'code' => $code ));

            foreach( $res as $query_result ) {
//                    $values .= implode( ',', $query_result->$get_param ) . ',';
//                    if ( $save_func ) {
//                        $values = '';
//                    }
                $result[] = $query_result;
            }
//                $result[] = $values;
            if ( count( $res->a24->$get_param ) < $offset_step )
                break;
//                echo '<br>' . count( explode( ',', $values )) . '<br>';
            sleep(0.5);
        }
        print_r( $result );
        return $values;
    }

}

class hf
{
    public $id;
    public $results;
    public $public_id;
    public $users_array;
    public $data_of_creation;

    public static function fill_hf( $id )
    {
        $hf = new hf;
        $hf->users_array = self::get_hf_data($id);
        $hf->data_of_creation = date('r');
        $hf->public_id = $id;
        hf_factory::save($hf);
        return $hf;
    }

    public static function get_hf_data( $public_id )
    {
        $params = array(
            'gid'   =>  $public_id,
            'count' =>  500,
            'sort'  =>  'time_desc'
        );

        $res = VkHelper::api_request( 'groups.getMembers', $params, 0 );
        if( isset( $res->error) || empty( $res )) {
            return false;
        }
        return $res->users;
    }

    public function get_changes()
    {
        $users_chunks = array_chunk( $this->users_array, 25 );
        $still_users_counter = 0;
        $start = 500;
        foreach( $users_chunks as $users_chunk ) {
//            echo 'left: ',($start -= 25), '<br>';
            $code =    'var uids=' .ObjectHelper::ToJSON($users_chunk) . ';
                        var still_users_count=0;
                        var i=0;
                        while(i<uids.length){
                            var uid=uids[i];
                            still_users_count = still_users_count + API.groups.isMember({"gid": ' . $this->public_id . ', "uid": uid});
                            i=i+1;
                        };
                        return {"still_users_count":still_users_count};';
            $res = VkHelper::api_request( 'execute', array('code' => $code));
            $still_users_counter += $res->still_users_count;
            sleep( VkHelper::PAUSE * 2 );
        }
        $this->results[] = date('H:i d.m.Y') . ', users: ' . $still_users_counter;
        echo '<br>';
        print_r($this->results);
        hf_factory::save( $this );
    }

}

class badoo_user
{
    /** @var $id Int*/
    public $id;
    /** @var $id Int*/
    public $external_id;
    /** @var $id String*/
    public $visits;
    /** @var $id DateTimeWrapper*/
    public $created_at;
    /** @var $id DateTimeWrapper*/
    public $updated_at;
    /** @var $id DateTimeWrapper*/
    public $last_visit;

    public function __construct($external_id = null, $visits = array(), $last_visit = null, $id = null )
    {

        $this->external_id = $external_id;
        $this->visits      = $visits;
        $this->last_visit  =  $last_visit;
        if( $id ) {
            $this->id = $id;
        }
    }

    public function add_visit( $visit_ts)
    {
        if(!in_array( $visit_ts, $this->visits))
            $this->visits[] = $visit_ts;
    }

}

class badoo_user_factory
{
    const profile_prefix ='http://badoo.com/0';
    private static  $table = 'badoo_users_month';
    public function save()
    {}
    /** @return badoo_user[]*/
    public static function get( $externalId )
    {
        $sql = 'select * from ' . self::$table . ' where external_id = ' . $externalId;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $res = array();
        while($ds->Next()) {
            $id = $ds->GetInteger('id');
            $res[$id] = new badoo_user(
                $ds->GetInteger('external_id'),
                $ds->GetComplexType('visits', 'int[]'),
                $ds->GetDateTime('last_visit'),
                $id
            );
        }
        return $res;
    }

    /** @return badoo_user[]*/
    public static function get_not_updated_chunk()
    {
        $sql = 'select * from ' . self::$table . ' where updated_at < now() - interval \'1 day\' OR updated_at is null limit 100';
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $res = array();
        while($ds->Next()) {
            $id = $ds->GetInteger('id');
            $res[$id] = new badoo_user(
                $ds->GetInteger('external_id'),
                $ds->GetComplexType('visits', 'int[]'),
                $ds->GetDateTime('last_visit'),
                $id
            );
        }
        return $res;
    }

    /** @return badoo_user[]*/
    public static function get_by_id( $id )
    {
        $sql = 'select * from ' . self::$table . ' where external_id = ' . $id;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $ds->Next();
        return new badoo_user(
            $ds->GetInteger('external_id'),
            $ds->GetString('visits'),
            $ds->GetDateTime('last_visit'),
            $ds->GetInteger('id')
        );
    }

    /** @var $badoo_user badoo_user*/
    public static function add( $badoo_user )
    {
        $sql = 'insert into ' . self::$table . '(external_id,visits,created_at, last_visit) VALUES (@external_id, @visits, @created_at, @last_visit)';
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
        $cmd->SetInt('@external_id', $badoo_user->external_id );
        $cmd->SetComplexType('@visits', $badoo_user->visits, 'int[]');
        $cmd->SetDateTime('@created_at', DateTimeWrapper::Now());
        $cmd->SetDateTime('@last_visit', $badoo_user->last_visit);
        return $cmd->Execute();
    }

    /** @var $badoo_user badoo_user*/
    public static function update( $badoo_user )
    {
        if($badoo_user->id) {
            $sql = 'UPDATE ' . self::$table . ' SET visits = @visits, last_visit = @last_visit, updated_at = @updated_at WHERE id=@id';
            $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
            $cmd->SetInt('@id', $badoo_user->id );
            $cmd->SetComplexType('@visits', $badoo_user->visits, 'int[]');
            $cmd->SetDateTime('@last_visit', $badoo_user->last_visit);
            $cmd->SetDateTime('@updated_at', DateTimeWrapper::Now());
            return $cmd->Execute();
        }
    }

    public static function get_period( $response )
    {
        $response = mb_strtolower($response);

        $search_arrays = array(
            'today'                  =>     array('час', 'минут', 'cейчас','пользователь', 'только что'),
            'yesterday'              =>     array('вчера'),
            'week'                   =>     array('недел'),
        );

        foreach ($search_arrays as $period => $search_array ) {
            foreach($search_array as $needle ) {
                if(mb_substr_count($response, $needle )) {
                    return $period;
                }
            }
        }
        $response = str_replace(
            array('августа','июля','июня','мая','апреля','марта','февраля','января','декабря','ноября','октября','сентября'),
            array('August','July','June','May','April', 'March', 'February','January','December', 'November','October','September' ),
            $response
        );
        $response = trim( str_replace(
            array('была на сайте','был на сайте'),
            '',
            $response
        ));
        return $response;
    }

    public static function get_ts_from_peiod( $period )
    {

        $more_then_week = new DateTimeWrapper('17.06.2013');
        $now = DateTimeWrapper::Now();
        switch($period) {
            case 'today':
                return $now->modify('midnight');
            case 'yesterday':
                return $now->modify('-1 day')->modify('midnight');
            case 'week':
                return $more_then_week;
            default:
                return new DateTimeWrapper($period);
        }
    }

    public static function get_last_checked_id()
    {
        $sql = 'select MIN(external_id) as min from ' . self::$table ;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();

        if($ds->Next())
            return $ds->GetInteger('min');
        return false;
    }

}

class hf_factory
{
    public static $mapping = array(
        'id'                =>  'integer',
        'results'           =>  'string array',
        'public_id'         =>  'integer',
        'users_array'       =>  'int array',
        'data_of_creation'  =>  'timestamp',
    );

    public static function save( hf $hf)
    {
        if( !$hf->id )
            $sql = 'INSERT INTO half_life VALUES (@public_id,@data_of_creation, @users_array, null )';
        else
            $sql = 'UPDATE half_life SET results = @results where id = @id';
        $sql .= ' returning id';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $cmd->SetInteger( '@id', $hf->id );
        $cmd->SetInteger( '@public_id', $hf->public_id );
        $cmd->SetDateTime('@data_of_creation', $hf->data_of_creation );
        $cmd->SetComplexType( '@results',     $hf->results, 'string[]');
        $cmd->SetComplexType( '@users_array', $hf->users_array, 'int[]');
        $ds = $cmd->Execute();
        echo $cmd->GetQuery(), '<br>';

        if( !$ds->Next())
            throw new Exception('failed to safe ' . ObjectHelper::ToJSON( $hf ));
        $hf->id = $ds->GetInteger('id');
    }

    /**
     * @var $search array
     * @return hf[]
     */
    public static function get( array $search )
    {
        $result = array();
        $sql = 'SELECT * FROM half_life where true ';

        foreach( $search as $query ) {
            if( !isset( self::$mapping[ $query[0]] ))
                continue;

            $sql .= ' AND ' . $query[0] . ' '. $query[1]. ' ' . (isset( $query[2]) ? $query[2] :' ') . ' ';
        }

        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();

        while( $ds->Next() ) {
            $hf = new hf();
            $hf->id                 = $ds->GetInteger( 'id' );
            $hf->results            = $ds->GetComplexType('results', 'string[]' );
            $hf->users_array        = $ds->GetComplexType('users_array', 'int[]' );
            $hf->public_id          = $ds->GetInteger('public_id');
            $hf->data_of_creation   = $ds->GetDateTime( 'id' );
            $result[] = $hf;
        }
        return $result;
    }
}

class Ceil
{
    public $link;
    public $value;

}



?>
