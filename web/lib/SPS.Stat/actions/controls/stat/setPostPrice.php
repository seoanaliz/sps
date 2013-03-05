<?
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.VK' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class setPostPrice {

        /**
         * Entry Point
         */

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
            foreach( $data_array as $public_id=>$public ) {
                $t = 0;
                $cell = $aSheet->getCellByColumnAndRow( $t++, $row );
                $cell->setValue( $public_id);
                $cell = $aSheet->getCellByColumnAndRow( $t, $row );
                $cell->setValue( $samorost );
                $total_vidget = 0;
                $public_visitors = 0;
                $public2  = array_reverse( $public, true );
                foreach( $public2 as $time => $value ) {
                    if( $time < $from )
                        continue;
                    if( $time > $to )
                        break;
                    $public_visitors +=  $value['unique_visitors'];
                    $total_vidget += $value['vidget_members'];
                    $total_views  += $value['unique_visitors'];
//                    $total_subs_coverage += $value['followers_coverage'];
//                    $total_full_coverage += $value['full_coverage'];
                    $cell = $aSheet->getCellByColumnAndRow( ++$t, $row );
                    $cell->setValue( ( $value['members_growth'] - $value['vidget_members'] - $samorost ));
                }
//                echo '<br>' .  $total_subs_coverage . ' | ' . $public_id ;

                $samorost = next( $this->samorost);
                $row++;
            }

//            echo '<br>';
//            echo  'full coverage = ' . $total_full_coverage;
//            echo  'subs coverage = ' . $total_subs_coverage;

//отдаем пользователю в браузер
            include("C:/wrk/classes/PHPExcel/Writer/Excel5.php");
            $objWriter = new PHPExcel_Writer_Excel5($pExcel);
////            header('Content-Type: application/vnd.ms-excel');
////            header('Content-Disposition: attachment;filename="rate.xls"');
////            header('Cache-Control: max-age=0');
            file_put_contents('c:/wrk/1.xls', '');
            $objWriter->save('c:/wrk/1.xls');

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


//                $res[$public_id] = $this->key_maker( 1 , 1, 1, 1, 1, $full_coverage, $followers_coverage );
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

        public function Execute()
        {
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

?>
