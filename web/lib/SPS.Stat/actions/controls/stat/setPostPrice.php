<?
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.VK' );

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
        if( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
            include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
            $a = new eug_stat();
            $a->Execute();
        }
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
        print_r($groupUserArray);
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

        die();
        $ar = array(
            'Путешествия'   => 'Путешествия',
            'Россия'        => 'Россия',
            'Европа'        => 'Европа',
            'Америка'       => 'Америка',
            'Азия'          => 'Азия',
            'Третий мир'    => 'Третий мир',
            'Острова'       => 'Острова',
            'Интересные отели' => 'Интересные отели',
        );
        $i = 2;
        foreach( $ar as $k=>$v){
            $sql = "insert into categories values(35806721,'".$k."','".$k."', $i)";
            $cmd = new SqlCommand($sql, ConnectionFactory::Get('tst'));
            $ds = $cmd->Execute();
            $i++;
        }
        die();
        if( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
            include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
            $a = new eug_stat();
            $a->Execute();
        }
        die();
        $targetFeeds = TargetFeedFactory::Get();

        foreach($targetFeeds as $feed) {
            if( $feed->targetFeedId < 100) {

                $feed->params['showTabs'] = array(
                    SourceFeedUtility::Albums       => 'on',
                    SourceFeedUtility::Authors      => 'on',
                    SourceFeedUtility::AuthorsList  => 'on',
                    SourceFeedUtility::My           => 'off',
                    SourceFeedUtility::Source       => 'on',
                    SourceFeedUtility::Topface      => 'on',
                    SourceFeedUtility::Ads          => 'on'
                );
                $feed->params['isOur'] = 'on';

            } else {
                $feed->params['showTabs'] = array(
                    SourceFeedUtility::Albums       => 'off',
                    SourceFeedUtility::Authors      => 'on',
                    SourceFeedUtility::AuthorsList  => 'on',
                    SourceFeedUtility::My           => 'off',
                    SourceFeedUtility::Source       => 'off',
                    SourceFeedUtility::Topface      => 'off'
                );
                $feed->params['isOur'] = 'off';
            }
        }
        TargetFeedFactory::UpdateRange( $targetFeeds);

        die();

        $a = new ParserFacebook();
        $a->get_posts('124727714210038','CAACEdEose0cBALzCm93BmLk4WsyODQngWwzV4zxid6RZAbmUdwPnmt6qkA6UKmjPCP0jlvbA3KhReX76chOHxqQAZCq9t8iIhJELFFKdlgR2optfnZBXbsSw1aqt8jMbbZAcNuLUfnyIBJZByEt0zDxj75BslV0QZD' );
        die();
        $fll=0;
        $writefn = function($ch, $chunk) use (&$fll){
            static $data='';
            static $limit = 20000;
            echo 1;
            $len = strlen($data) + strlen($chunk);
            if ($len >= $limit ) {
                $data .= substr($chunk, 0, $limit-strlen($data));
                echo strlen($data) , ' ', $data;
                return -1;
            }

            $data .= $chunk;
            return strlen($chunk);
        };

        $url = 'http://badoo.com/0282205314/';
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $writefn);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RANGE, '0-1000');
        curl_setopt($ch, CURLOPT_TIMEOUT , 3 );
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "<br>error in curl: ". curl_error($ch) ."<br>";
            return 'error in curl: '. curl_error($ch);
        }

        curl_close($ch);
        file_put_contents('c:/wrk/3.txt',$result );

        die();
        header("Content-Type: text/html; charset=UTF-8");
        set_time_limit(10000);
        $this->get_barter_activity();

//
//            $this->get_barter_stat_by_public();
//            die();
//            print_r( VkHelper::multiget(array('http://badoo.com/0306023034/')));
//
//
////            VkHelper::api_request('get.stats');
//            die();
//
//
//            $check_chunk = badoo_user_factory::get_not_updated_chunk();
//
//            while(!empty($check_chunk)) {
//                $check_chunk = ArrayHelper::Collapse( $check_chunk, 'external_id', false);
//
//                $users_ids   = array_keys( $check_chunk );
//                $prefix = badoo_user_factory::profile_prefix;
//                array_walk($users_ids, function(&$k) use($prefix){
//                    $k = $prefix.$k;
//                });
//
//                $res = VkHelper::multiget($users_ids);
//                foreach( $res as $url => $result) {
//                    $matches = array();
//                    if( preg_match('/class="psnc_str">(.*?)</', $result, $matches )) {
//                        $id = explode('/',$url);
//                        $id = end($id);
//                        $id = ltrim($id, '0');
//                        print_r($matches[1]);
//                        $period = badoo_user_factory::get_period($matches[1]);
//                        $last_visit = badoo_user_factory::get_ts_from_peiod($period);
//                        if( empty($check_chunk[$id]->visits)) {
//                            $check_chunk[$id]->visits[] = $check_chunk[$id]->last_visit->format('U');
//                        }
//                        $check_chunk[$id]->last_visit = $last_visit;
//                        $check_chunk[$id]->add_visit($last_visit->format('U'));
//                        badoo_user_factory::update($check_chunk[$id]);
//                    } elseif( preg_match('/Document moved: .. href="(.*?)">/', $result, $matches)) {
//                        if( trim( $matches[1]) != 'http://badoo.com/' )
//                            $reget_array[] = $matches[1];
//                    }
//                }
//                $check_chunk = badoo_user_factory::get_not_updated_chunk();
//            }
//            die();


//            $a = range(1000, 1450);
//            $a = implode(',', $a);
//            $start = microtime(1);
//            for( $i = 0; $i < 100; $i++ ) {
//                $params = array(
//                    'gids'      =>  $a,
//                    'fields'    =>  'members_count'
//                );
//                $res = VkHelper::api_request('groups.getById', $params,1);
//                if(isset($res->error->error_message)) {
//                    echo $res->error->error_message;
//                    die();
//                }
//                print_r( count( $res ));
//                echo '<br>';
//            }
//            echo (microtime(1) - $start);
//
//            die();
//новые юзеры в баду
//
//            $day_diff = 328371225;
//
//            $month_diiff = 5400000;
//            $i = 0;
//            while( $i < 100 ) {
//                $reget_array = array();
//                $id_start = badoo_user_factory::get_last_checked_id();
//                if ( !$id_start)
//                    $id_start = 328209453;
//                print_r($id_start);
//
//    //            $ch = curl_init( );
//    //            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    //            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//    //            cu    rl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//    //            curl_setopt($ch, CURLOPT_TIMEOUT , 3 );
//                $m = microtime(1);
//                $urls = array();
//                $url = 'http://badoo.com/0';
//                for($id = $id_start; $id > $id_start - 50; $id--){
//                    $urls[] = $url . $id;
//                }
//                $urls = $urls + $reget_array;
//
//                $res = VkHelper::multiget($urls);
//    //            curl_setopt( $ch, CURLOPT_URL, $url . $id );
//    //            $res = curl_exec($ch);
//                $stt = '';
//                foreach( $res as $url => $result) {
//                    $matches = array();
//                    $stt .= $result;
//                    if( preg_match('/class="psnc_str">(.*?)</', $result, $matches )) {
//                        $id = end(explode('/',$url));
//                        $id = ltrim($id, '0');
//                        print_r($matches[1]);
//                        $period = badoo_user_factory::get_period($matches[1]);
//                        $last_visit = badoo_user_factory::get_ts_from_peiod($period);
//                        $a = new badoo_user($id, $last_visit->format('U'), $last_visit);
//                        badoo_user_factory::add($a);
//                        echo '<br>';
//                    } elseif( preg_match('/Document moved: .. href="(.*?)">/', $result, $matches)) {
//                        if( trim( $matches[1]) != 'http://badoo.com/' )
//                            $reget_array[] = $matches[1];
//                    }
//                }
//                file_put_contents('c:/wrk/2.txt', $stt);
////                if(!empty($reget_array)) {
////                    $res = VkHelper::multiget($reget_array);
////                    foreach( $res as $url => $result) {
////                        if( preg_match('/class="psnc_str">(.*?)</', $result, $matches) &&
////                            preg_match( '/com.(\d*)."/', $result, $id)) {
////                            print_r($matches[1]);
////                            $period = badoo_user_factory::get_period($matches[1]);
////                            $last_visit = badoo_user_factory::get_ts_from_peiod($period);
////                            $a = new badoo_user($id[1], $last_visit->format('U'), $last_visit);
////                            badoo_user_factory::add($a);
////                            echo '<br>';
////                        }
////                    }
////                }
////
//                print_r($reget_array);
//
//                echo '<br>';
//                print_r(round(microtime(1) - $m));
//                echo '<br>';
//                sleep(7);
//                $i++;
//            }
//            die();
        $id_start = 328209453;
//            while( $i < 100 ) {
//                $reget_array = array();
//                $id_start = 0;
//
//
//                print_r($id_start);
//
//                $m = microtime(1);
//                $urls = array();
//                $url = 'http://badoo.com/0';
//                for($id = $id_start; $id > $id_start - 50; $id--){
//                    $urls[] = $url . $id;
//                }
//                $urls = $urls + $reget_array;
//
//                $res = VkHelper::multiget($urls);
//                $stt = '';
//                foreach( $res as $url => $result) {
//                    $matches = array();
//                    $stt .= $result;
//                    if( preg_match('/class="psnc_str">(.*?)</', $result, $matches )) {
//                        $id = end(explode('/',$url));
//                        $id = ltrim($id, '0');
//                        print_r($matches[1]);
//                        echo '<br>';
//                    } elseif( preg_match('/Document moved: .. href="(.*?)">/', $result, $matches)) {
//
//                    }
//                }
//
//
//                print_r(round(microtime(1) - $m));
//                echo '<br>';
//                sleep(0.01);
//                $i++;
//            }
//            $client = new Google_Client();
//            $client->setApplicationName('socialboard');
//
//            $client->setClientId('567544131410.apps.googleusercontent.com');
//            $client->setClientSecret('AIzaSyDNx-cTCtsEjcJZkpqjddjSw-rGXbW-4jk');
//            $client->setRedirectUri('blank.html');
//            $client->setDeveloperKey('AI39si4GudXqYPUyBaxp2wFz5GRGWmo9N-IO7_T61DCA3fVIw7k6V7FvOAOy-MVJ4_qkX6NdKTDHIxUvzE76S4kq9CVzE384gQ');
//            $plus = new Google_PlusService($client);
//            $token = $client->getAccessToken();
//            $client->authenticate();

//            if (isset($_SESSION['access_token'])) {
//                $client->setAccessToken($_SESSION['access_token']);
//            }
//            $me = $plus->people->get(112021814591089360865);
//
//            $optParams = array('maxResults' => 100);
//            print_r($me);

        if( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
            include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
            $a = new eug_stat();
            $a->Execute();
        }
        die();
        $post_data['photo_array'] = array('C:/Users/user/Desktop/GBOPBwehVeU.jpg');
        $post_data['text'] = 'tsts';
        $post_data['group_id']  = 43503235;
        $post_data['vk_access_token'] = '67cf9566bac3a7b9e03a49037efdbe06f2821122d99139ae52ac2aa43247718d48937ee1410e7880af71c';
        $a = new SenderVkontakte( $post_data );
        $a->send_post();
        die();
        $ab = new ParserTop();
        $targetFeeds = TargetFeedFactory::Get();

        foreach($targetFeeds as $feed) {
            if( $feed->targetFeedId < 100) {

                $feed->params['showTabs'] = array(
                    SourceFeedUtility::Albums       => 'on',
                    SourceFeedUtility::Authors      => 'on',
                    SourceFeedUtility::AuthorsList  => 'on',
                    SourceFeedUtility::My           => 'off',
                    SourceFeedUtility::Source       => 'on',
                    SourceFeedUtility::Topface      => 'on',
                    SourceFeedUtility::Ads          => 'on'
                );
                $feed->params['isOur'] = 'on';

            } else {
                $feed->params['showTabs'] = array(
                    SourceFeedUtility::Albums       => 'off',
                    SourceFeedUtility::Authors      => 'on',
                    SourceFeedUtility::AuthorsList  => 'on',
                    SourceFeedUtility::My           => 'off',
                    SourceFeedUtility::Source       => 'off',
                    SourceFeedUtility::Topface      => 'off'
                );
                $feed->params['isOur'] = 'off';
            }
        }
        TargetFeedFactory::UpdateRange( $targetFeeds);
        die();

        print_r($_SESSION);
        Session::setBoolean('sdfsdf', true);
        print_r($_SESSION);
        die();

        $targetFeed = TargetFeedFactory::GetOne(array('targetFeedId' => 75));

        $targetFeed->params['showTabs'] = array(
            SourceFeedUtility::Albums       => true,
            SourceFeedUtility::Authors      => true,
            SourceFeedUtility::AuthorsList  => true,
            SourceFeedUtility::My           => true,
            SourceFeedUtility::Source       => true,
            SourceFeedUtility::Topface      => true
        );
        print_r( $targetFeed );

        TargetFeedFactory::Update($targetFeed);

        die();
        if( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
            include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
            $a = new eug_stat();
            $a->Execute();
        }
        die();

        $a = new ParserTop();
        $a->get_cities_mk2();
        die();

        set_time_limit(30);
        $start =  microtime(1);
        for ( $i = 0; $i < 1000; $i++) {
            $a = new test(1);
        }
        echo '<br>',round( microtime(1) - $start, 4 );
        $start =  microtime(1);
        for ( $i =0; $i < 1000; $i++) {
            $b = test2::getInstance(1);
        }
        echo '<br>',round( microtime(1) - $start, 4 );
        die();
//            print_r( $this->get_barter_activity());
//
//            die();
//            $from = Request::getDateTime( 'from' );
//            $public_id = Request::GetInteger( 'public_id' );
//            $this->get_barter_stat_by_public( $public_id, $from, 'old' );
//            die();
//            if( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
//                include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
//                $a = new eug_stat();
//                $a->Execute();
//            }
//            die();
        //helps tp move groups
        // 1'update groups set status = null where created_by is not null'
        //2 move old-style stat groups to brand new
        $sql = 'select * from stat_groups';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $ds  = $cmd->Execute();
        $publ_id_rel = array();
        $newGroups = array();
        while( $ds->Next()) {
            $publ_id_rel[$ds->GetInteger('vk_id')] = $ds->GetInteger('vk_public_id');
            $group = new Group();
            $group->name = $ds->GetValue('name');
            $group->source = Group::STAT_GROUP;
            $group->status = $ds->GetInteger('group_id');
            $group->type   = $ds->GetBoolean('general') ? GroupsUtility::Group_Global : GroupsUtility::Group_Private;
            if( $ds->GetValue( 'type' ) == 2)
                $group->type = GroupsUtility::Group_Shared_Special;
            $group->general= $ds->GetBoolean('general');
            $newGroups[] = $group;
        }
        foreach ( $newGroups as $group_tmp){
            print_r($group_tmp);
            echo '<br>';
            if( !GroupFactory::Add( $group_tmp )){
                print_r($group_tmp);
                die();
            }
        }
//            GroupFactory::AddRange( $newGroups );
        print_r($newGroups);
        die();

//            $groups = GroupFactory::Get();
//            print_r( $groups);
//            die();
//            ArrayHelper::Collapse($groups, 'status');
//            //3 move old-style stat group-public relation to brand new
//            $sql = 'select * from stat_publics_50k';
//            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
//            $ds  = $cmd->Execute();
//            $publ_id_rel = array();
//            while( $ds->Next()) {
//                $publ_id_rel[$ds->GetInteger('vk_id')] = $ds->GetInteger('vk_public_id');
//            }
//
//            $sql = 'select * from stat_group_public_relation';
//            $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
//            $ds = $cmd->Execute();
//            $groups = array();
//            while( $ds->Next()) {
//                $group_id = $ds->GetInteger('group_id');
//                if(!$group_id) {
//                    print_r($ds);
//                    continue;
//                }
//                if( !isset($group_id) )
//                    $groups[$group_id] = array();
//
//                if( isset($publ_id_rel[$ds->GetInteger('public_id')]))
//                    $groups[$group_id][] = $publ_id_rel[$ds->GetInteger('public_id')];
//                else
//                    echo $ds->GetInteger('public_id'), '<br>';
//            }
//
//            $stat_groups_for_update = array();
//            foreach( $groups as $id => $publics_ids ) {
//                if( $id && !empty( $publics_ids )) {
//                    $group = GroupFactory::GetOne( array( 'status'=> $id ));
//                    if( $group ) {
//                        $group->entries_ids = $publics_ids;
//                        $stat_groups_for_update[] = $group;
//                    }
//                }
//            }
//
//            foreach( $stat_groups_for_update as $group_fu ) {
//                print_r( $group_fu );
//                echo '<br>';
//                GroupFactory::Update($group_fu);
//            }
//            die();
        //4 move old-style stat group-user relation to brand new

        $groups = GroupFactory::Get(array('source' => 3));
        $groups=  ArrayHelper::Collapse( $groups, 'status', false);

        $sql = 'select * from stat_group_user_relation';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
        $ds = $cmd->Execute();
        $users = array();
        while( $ds->Next()) {
            $user_id = $ds->GetInteger('user_id');
            if(!$user_id) {
                continue;
            }
            if( !isset($user_id) )
                $users[$user_id] = array();
            $old_group_id = $ds->GetInteger('group_id');
            if( isset( $groups[$old_group_id])) {
                $users[$user_id][] = $groups[$old_group_id]->group_id;
            }
        }

        unset( $groups);
        $stat_users_for_update = array();
        foreach( $users as $id => $groups ) {
            if( $id && !empty( $groups )) {
                $statUser = StatUserFactory::GetOne(array('user_id'=> $id));
                if( $statUser ) {
                    $statUser->groups_ids = $groups;
                    $stat_users_for_update[] = $statUser;
                }
            }
        }
        StatUserFactory::UpdateRange($stat_users_for_update);
        die();


        //5 change user_id from vk to our id


        $post_data['photo_array'] = array('C:/Users/user/Desktop/GBOPBwehVeU.jpg');
        $post_data['text'] = '#заслуживаетЛайк@vk.sportlife';
        $post_data['group_id']  = 27421965;
        $post_data['vk_access_token'] = '312702787db7231c54502a28d96ccf408945046f1bab664a796389e43d6699fa66cd42f8d912306e29ca4';
        $a = new SenderVkontakte( $post_data );
        $a->send_photo_in_album('икро');
        die();
        $code =    'var c = API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0];
                        return {me: c};';

        $result = VkHelper::api_request('execute', array( 'code'=> $code ));
        print_r( $result );
        die();
        $from =  $_REQUEST['from'];
//            $this->get_barter_stat_by_public( '52223807', $from );

        if( isset( $_REQUEST['from']) && isset( $_REQUEST['to'])) {
            include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
            $a = new eug_stat();
            $a->Execute();
        }
        die();
        if( isset( $_REQUEST['half_life'] )) {
            $this->half_life();
        }

        echo 'С какого числа делаем? <input type="date" >',
        'по какое число ? <input type="date" >', '<br>';
        echo '<a href="/?halflife">собрать стату по полураспаду</a><br>';
        echo '<a href="/?halflife=0&update=0">обновить стату по полураспаду</a>';
        echo '<a href="/?halflife=0&update=0">обновить стату по полураспаду</a>';

        die();

        $a = array('1','2','3','4','5','6','7','h','i','j');
        $b = array('A','B','c','D','E','F','G','H','I','J');
        $start = microtime(true);

        for( $i = 0; $i < 1000000 ; $i++) {
            $res = array_intersect( $a, $b );
        }

        print_r($res);
        echo '<br>start 1st eep', (microtime(1) - $start),'<br>';


        $start = microtime(true);
        $a = array_flip($a);
        $b = array_flip($b);

        for( $i = 0; $i < 1000000 ; $i++) {
            $res = array_intersect_key( $a, $b );
        }

        print_r($res);
        echo '<br>start 1st ', (microtime(1) - $start),'<br>';


        die();
        $a = array(1,2,3);
        $b = array(2,'3',4,5);
        print_r($a+$b );
        die();
        $text = 'Давно выяснено, что при оценке дизайна и композиции. читаемый текст мешает сосредоточиться. Lorem Ipsum используют потому, что тот обеспечивает более или менее стандартное заполнение шаблона,
 а также реальное распределение. букв и пробелов в абзацах, которое не получается при простой ду бликации "Здесь ваш текстdfsad Fsfsdfsdfsdfsdfs sd sdsdf sadfds. ff sdf sdsdfsdf sdf sdf sdf sdf sdf sadfasf asdf asdf sdf safsa f sdf dsfdnf dsahndhlkjfhaslfhlsakhf lksadhf lkdsasf. Давно выяснено, что при оценке дизайна и композиции читаемый текст мешает сосредоточиться. Lorem Ipsum используют потому, что тот обеспечивает более или менее стандартное заполнение шаблона, а также реальное распределение букв и пробелов в абзацах, которое не получается при простой дубликации "Здесь ваш текст..Давно выяснено, что при оценке дизайна и композиции читаемый текст мешаетДавно выяснено, что при оценке дизайна и Давно выяснено, что при оценке дизайна и композиции читаемый текст мешает сосредоточиться. Lorem Ipsum используют потому, что тот обеспечивает более или менее стандартное заполнение шаблона, а также реальное распределение букв и пробелов в абзацах, которое не получается при простой дубликации "Здесь ваш текст.. Давно выяснено, что при оценке дизайна и композиции читаемый текст мешает сосредоточиться. Lorem Ipsum используют потому, что тот обеспечивает более или менее стандартное заполнение шаблона, а также реальное распределение букв и пробелов в абзацах, которое не получается при простой дубликации "Здесь ваш текст..Давно выяснено, что при оценке дизайна и композиции читаемый текст мешает сосредоточиться. Lorem Ipsum используют потому, что тот обеспечивает более или менее стандартное заполнение шаблона, а также реальное распределение букв и пробелов в абзацах, которое не получается при простой дубликации "Здесь ваш Давно выяснспределение букв и пробелов в абзацах, которое не получается при простой дубликации "Здесь ваш Давно выяснспределение букв и пробелов в абзацах, которое не получается при простой дубликации "Здесь ваш Давно выясн';

        $start_pos = 300;
        $stop_pos = 420;
        $search_string_slice = substr( $text, $start_pos, $stop_pos -  $start_pos);
        echo strlen($search_string_slice),'<br>';

        if ( $limit = strrpos( $search_string_slice, "\n")) {}
        elseif( $limit = strrpos( $search_string_slice, '. ') ) {}
        elseif( $limit = strrpos( $search_string_slice, ', ') ) {}
        else { $limit = $stop_pos - $start_pos; }
        $limit += $start_pos;
        print_r(substr( $text, 0, $limit ));
//            $post_data['photo_array'] = array();
//            $post_data['text'] = '#заслуживаетЛайк@vk.sportlife';
//            $post_data['group_id']  = 27421965;
//            $post_data['vk_access_token'] = '0e555843b0e6e2c91ab85c1130776bb2b7bb576660f01cff5fb4b00822bebdea5b0051bffe1b903fa7f71';
//            $a = new SenderVkontakte( $post_data );
//            $a->send_post();
        die();
        include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
        $a = new eug_stat();
        $a->Execute();
        die();

        $res = VkHelper::api_request( 'execute', $code, 0 );
        print_r($res);
        die();
        $code .= trim( $return, ',' ) . "};";
        $res = VkHelper::api_request( 'execute', array( 'code' => $code ), 0, $app );
        die();
        $json_array = explode( '"},{"',$s);
        print_r(count( $json_array));
        foreach( $json_array as $string ) {
            $f = json_decode( '{"' . $string . '"}' );
            print_r($f);
            if ($f) continue;
            print_R($string);
            die();
        }
        die();
        $repostOrigin = '-23123211_231313';
        $originPublicId = current(explode('_', trim( $repostOrigin, '-')));
        print_r($originPublicId);
        die();
        $a = file_get_contents('c:/wrk/1.txt');
        print_r($a);
        $k = json_decode($a);
        print_r( $k );
        die();
        $sql = 'update "articleRecords" set content = replace(content,\'Чuтaть п\', \'П\')';
        $sql = 'select count(*) from "articleRecords"  where content LIKE \'%Чuтaть п%\'';
        $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
        $ds = $cmd->Execute();
        $ds->Next();
        print_r($ds->GetInteger('count'));
        die();
        include 'C:\wrk\sps\web\lib\SPS.Stat\actions\controls\eug_stat\eug_stat.php';
        $a = new eug_stat();
        $a->Execute();
        die();
        set_time_limit(10);
        $current_ceil = new Ceil;
        $current_ceil->value = 12;
        $first_ceil_link = $current_ceil;
        for( $i = 1; $i < 10; $i++) {
            $next_ceil = new Ceil;
            $next_ceil->value = $i;
            $current_ceil->link = &$next_ceil;
            $current_ceil = $next_ceil;
        }
        $object = $first_ceil_link;
        while( 1 ) {
            echo $object->value, '<br>';
            if(!isset($object->link))
                break;
            $object = $object->link;
        }
        //реверс
        die();
        unset( $next_ceil );
        $next_ceil = null;
        while( 1 ) {
            $tmp_object = $object->link;
            print_r($tmp_object);
            $object->link = $next_ceil;
            $object = $tmp_object;
            print_r($object);
            $next_ceil = $tmp_object;
            if(!$object->link)
                break;
        }
        echo '<br><br>';
        die();
        while(1) {
            print_r($object->value);
            if(!isset($object->link))
                break;
            $object = $object->link;

        }

        die();

////            $sql = 'update "articleRecords" set content = replace(content,\'Чuтать п\',\'П\') where content LIKE \'%Чuтать п%\'';
//            $sql = 'SELECT COUNT(*) FRoM "articleRecords"  where content LIKE \'%Чuтать п%\'';
//            $cmd = new SqlCommand( $sql, CommentFactory::Get() );
//            $ds =             $cmd->Execute();
//      $ds->Next();
//            print_R($ds->GetValue('count'));
        die();
        $this->get_authors_stats_blabla();
        die();
        $this->get_public_stats_wo_api();
        die();

//            print_r( StatPublics::get_our_publics_list());
//
//            die();
//            $data = 'AGACGGTGCTTGGGAGGGAGTCTTGCTAGGGACGGTGGACGGACCTTGAAGGTTATGGCTAGATTGGTAGAGACAGGGTGATGGATTGGGATGGTTATACCTCGCTTCCCCAATCCACTTCTAAGAACCCAGGCGGCCCTTTTACGCGTAACGTAGATTCGTCTACCCACGAAATCGTTGGTAGCTATCTTTGGATTCGTTGTATACGGGAAAAGAGACTGAGACCCCCTCCTGTCTCACAGTCGCCTCGTTTGTCTCCGAGCGCTATCCAGCTATCGAAATCTGCGTTTGCGGGCAGTTTAAACGCTTACCATGCGCGCAATATAGGTAACAGCGCCTTTGTGACTTCATTAACTTAATGATGAACAATAACGACGTATCGTCGGTCCATCGAGCAAGAACTTGTGCACCTAGGTTCGACGTTGCCGAACTGCGGAGTATTAGATTGGAGCAGTATAGTCTACGTGGTTCACGTCTCCTCCCAGTCGACAGCGGGTATCAACATCGGTAAAACAGTACAGTGGTAACTTACCTAAAATTACTCCGACAATAACCGCCTAAGGCCACACAACTCTGGCTAATCATTATCAAGAACCCTAACATCCGGCCTTATTAAGGCTGAGCTGAGAGATGTGGACATTACTGGGTAATCCTTCCCTAGCTCCCGTTCGGCTGCGGTACTATCCTAGTTGTAAGGCCCGCCTTGACTTGCCACCAGGTTCTGCTGTAGTTTAGACCTTGGACATATTCGGAGCGCGTGAGGGCAGAATGTGATGCACGCTTTGAGGGCGACGCACCGAGTATACTCGGCAATTACAGCGTAGTTCGGATTTAGGACTGGAAGTCTTGGGTGGCACGGTGTTAAGACGGATAGACCACATATAGAGTTCGGTCGCCAGTGGCGC
//';
//            $length = strlen( $data );
//            $res = array();
//            for( $i = 0; $i < $length; $i++ ) {
//                if( !isset( $res[$data[$i]]))
//                     $res[$data[$i]] = 1;
//                else
//                    $res[$data[$i]]++;
//            }
//            foreach($res as $number)
//                echo $number . ' ';
//            print_r($res);
//            die();
        $this->get_public_stats_wo_api();
        die();
        $this->post_photo_array =   isset( $post_data['photo_array'] ) ? $post_data['photo_array'] : array();
        $this->post_text        =   $this->text_corrector( $post_data['text'] );
        $this->vk_group_id      =   $post_data['group_id'] ;
        $this->vk_app_seckey    =   $post_data['vk_app_seckey'];
        $this->vk_access_token  =   $post_data['vk_access_token'];
        $this->audio_id         =   isset( $post_data['audio_id'] ) ? $post_data['audio_id'] : array();//массив вида array('videoXXXX_YYYYYYY','...')
        $this->video_id         =   isset( $post_data['video_id'] ) ? $post_data['video_id'] : array();//массив вида array('audioXXXX_YYYYYYY','...')
        $this->link             =   $post_data['link'];
        $this->header           =   $post_data['header'];
        $post = array(
            'photo_array' => array( '' )
        );

        die();
        $check = DialogFactory::Get(array( 'user_id' => 670456, 'rec_id' => 670456 ));
        print_r($check);
        die();
        for( $i = 0 ; $i < 20;$i++ )
        {
            $at = VkHelper::api_request('wall.get', array(),0);
            print_r ( $at );

        }
        die() ;
//            error_reporting( 0 );
        $a = new ParserVkontakte( 35807213 );
        print_r( $a->get_posts( 0 ));
        die();
//                for ($i = 1090582; $i > 1085582; $i-- ) {
//                   $line .= ',' . $i;
//                    $t++;
//                }
//            print_r( AdminsWork::get_public_authors(22));
        AdminsWork::get_author_final_score( 26, '36621560,
35806186,
36621513,
36621543,
35806721,
35806476,
35807148,
35807284,
35807216,
35807044,
35806378,
35807199,
35807078,
36959676,
36959733,
36959798,
36959959,
37140910,
37140977,
25678227,
37140953,
26776509,
38000555' );

//            echo microtime(1) . ' >>>>>>>>>>>>>>><br>' ;
//
//            echo '<br><<<<<<<<<<<<<<<' . microtime(1);

        die();
        foreach( $publics as $public_id ) {
            echo 'public_id = ' . $public_id . '<br>';
            StatPublics::get_public_users( $public_id, 'tst' );
        }
        die();
        $dialogs = MesDialogs::get_all_dialogs( 670456, 200 );
        print_r( $dialogs );
        die();
        StatUsers::set_mes_limit_ts( 670456 );

        die();
        echo '<table>';
        foreach( $table as $row ) {
            $row = '<tr>' . implode( '<td>', $row ) . '</tr>';
            echo $row;
        }

        echo '</table>';
        die();
        $publics = StatPublics::get_50k( 0, 37140953 );
        foreach( $publics as $public_id ) {
            StatPublics::get_public_users( $public_id, 1 );
            sleep(0.2);
        }
        die();

        $public_id   =   Request::getInteger( 'publId' );
        if (empty( $public_id )) {
            echo ObjectHelper::ToJSON(array( 'response' => false ));
            die();
        }

//            StatPublics::truncate_table( TABLE_TEMPL_USER_IDS );
//            StatPublics::truncate_table( TABLE_TEMPL_PUBLIC_SHORTNAMES );

        $users_array = StatPublics::get_distinct_users();

        $a = StatPublics::collect_fave_publics( $users_array );
        print_r ( $a );
        die();
        $publicId   =   Request::getInteger( 'publId' );
        $price      =   Request::getInteger( 'price' );

        if (empty($publId)) {
            echo ObjectHelper::ToJSON(array('response' => false));
            die();
        }
        $price = $price ? $price : 0;

        $query = 'UPDATE ' . TABLE_STAT_PUBLICS . ' SET price=@price WHERE vk_id=@publ_id';
        $cmd = new SqlCommand( $query, ConnectionFactory::Get( 'tst' ));
        $cmd->SetInteger( '@publ_id', $publicId );
        $cmd->SetInteger( '@price',   $price );
        $cmd->Execute();

        echo ObjectHelper::ToJSON(array( 'response' => true ));
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
