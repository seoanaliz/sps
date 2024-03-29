<?php
        /**
         * Array Helper
         * @package SPS
         * @subpackage Stat
         */

    //    define ( 'ACC_TOK_WRK', 'b03d241fb0371ee7b0371ee7b6b01c4063bb037b0222679cb604e99dfff088b' );
        define ( 'ACC_TOK_WRK', '0b8c8e800086894200868942b100a9af1a000860093b1dc50eb180b9b836874e8ec5f99' );
        define ( 'VK_API_URL' , 'https://api.vk.com/method/' );


        class AccessTokenIsDead extends Exception{}

        class   VkHelper {

            const PERM_NOTIFY = 1;             //Пользователь разрешил отправлять ему уведомления.
            const PERM_FRIENDS = 2;            //Доступ к друзьям.
            const PERM_PHOTO = 4;              //Доступ к фотографиям.
            const PERM_AUDIO = 8;              //Доступ к аудиозаписям.
            const PERM_VIDEO = 16;             //Доступ к видеозаписям.
            const PERM_APPS = 32;              //Доступ к предложениям.
            const PERM_QUESTIONS = 64;         //Доступ к вопросам.
            const PERM_WIKI = 128;             //Доступ к wiki-страницам.
            const PERM_LEFTMENU = 256;         //Добавление ссылки на приложение в меню слева.
            const PERM_QUICKPUBLISH = 512;     //Добавление ссылки на приложение для быстрой публикации на стенах пользователей.
            const PERM_STATUS = 1024;          //Доступ к статусам пользователя.
            const PERM_NOTES = 2048;           //Доступ заметкам пользователя.
            const PERM_MSG_EXTENDED = 4096;    //(для Desktop-приложений) Доступ к расширенным методам работы с сообщениями.
            const PERM_WALL = 8192;            //Доступ к обычным и расширенным методам работы со стеной.
            const PERM_ADS = 32768;            //Доступ к функциям для работы с рекламным кабинетом.
            const PERM_OFFLINE = 65536;        //Оффлайн-доступ
            const PERM_DOCS = 131072;          //Доступ к документам пользователя.
            const PERM_GROUPS = 262144;        //Доступ к группам пользователя.
            const PERM_NOTIFY_ANSWER = 524288; //Доступ к оповещениям об ответах пользователю.
            const PERM_GROUP_STATS = 1048576;  //Доступ к статистике групп и приложений пользователя, администратором которых он является.
            const INTERVAL_BETWEEN_MOVED_POSTS = 300;

            /**
             *id аппа статистки
             */
            const APP_ID_STATISTICS = 2642172;
            const ALERT_TOKEN = "9a52c2c5ad3c3a0dba10d682cd5e70e99aea7ca665701c2f754fb94e33775cf842485db7b5ec5fb49b2d5";
            const ANTIGATE_KEY  =   'cae95d19a0b446cafc82e21f5248c945';
            const FALSE_COUNTER = 3;
            const TESTING = true;
            const MEM_TOKENS_KEY = 'serv_access_tokens';

            /**
             *id аппа обмена
             */
            const APP_ID_BARTER = 3391730;
            const PAUSE   = 0.5;

            public static $tries = 0;
            public static  $serv_bots = array(
                array(
                    'login'     =>  '79531648056',
                    'pass'      =>  'SdfW3@4R4$'
                ),
                array(
                    'login'     =>  '79531648839',
                    'pass'      =>  'Kjhy&^d^9h'
                ),
                array(
                    'login'     =>  '79531647915',
                    'pass'      =>  'JHh97)&%lui'
                ),

            );

            public static $contentTypeName = array(
                'photo' =>  'pid',
                'video' =>  'vid',
                'audio' =>  'aid',
                'doc'   =>  'did',
                'poll'  =>  'poll_id',
            );

            public static  $open_methods = array(
                'wall.get'          => true,
                'groups.getById'    => true,
                'wall.getById'      => true,
                'photos.getAlbums'  => true,
                'groups.getMembers' => true
            );

            public static function api_request( $method, $request_params, $throw_exc_on_errors = 1, $app = '' )
            {
                if(isset($request_params['captcha_key'])) {
                    echo $request_params['captcha_key'], '<br>';
                }
                $app_id = $app == 'barter' ? self::APP_ID_BARTER : self::APP_ID_STATISTICS;
                if ( !isset( $request_params['access_token']) && !isset( self::$open_methods[ $method ]))
                    $request_params['access_token']  =  self::get_service_access_token( $app_id );
                $url = VK_API_URL . $method;
                $a = VkHelper::qurl_request( $url, $request_params );
                $res = json_decode(  $a );
                if( !$res )
                    return array();
                if ( isset( $res->error ) ) {
                    if( $res->error->error_code == 14 ) {
                        self::$tries++;
                        if( self::$tries > 3 ) {
                            self::$tries = 0;
                            echo self::$tries, '<br>';
                            throw new Exception('Error : cant get through captcha. ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ));
                        }
                        $request_params['captcha_key'] =  self::captcha( $res->error->captcha_img );
                        $request_params['captcha_sid'] =  $res->error->captcha_sid;
                        $res = self::api_request( $method, $request_params, $throw_exc_on_errors, $app );
                        return $res;
                    }
                    if ( $throw_exc_on_errors ) {
                        if( $res->error->error_code == 5 )
                            throw new AccessTokenIsDead();
                        else
                            throw new Exception('Error : ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ) );
                    } else {
                        return $res;
                    }
                }
                return $res->response;
            }

            public static function qurl_request( $url, $arr_of_fields, $headers = '', $uagent = '')
            {
                if (empty( $url )) {
                    return false;
                }
                $ch = curl_init( $url );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT , 180 );

                if (is_array( $headers )) { // если заданы какие-то заголовки для браузера
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }

                if (!empty($uagent)) { // если задан UserAgent
                    curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
                } else{
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1)');
                }

                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                if (is_array( $arr_of_fields )) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_of_fields));

                } else return false;

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo "<br>error in curl: ". curl_error($ch) ."<br>";
                    return 'error in curl: '. curl_error($ch);
                }

                curl_close($ch);
                return $result;
            }

            public static function get_vk_time( $access_token = '' )
            {
                return self::api_request( 'getServerTime', array( 'access_token' =>  $access_token ), $throwException = 0 );
            }

            public static function multiget( $urls, &$result )
            {
                $timeout = 20; // максимальное время загрузки страницы в секундах
                $threads = 20; // количество потоков

                $all_useragents = array(
                    "Opera/9.23 (Windows NT 5.1; U; ru)",
                    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.4;MEGAUPLOAD 1.0",
                    "Mozilla/5.0 (Windows; U; Windows NT 5.1; Alexa Toolbar; MEGAUPLOAD 2.0; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7;MEGAUPLOAD 1.0",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Maxthon; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; InfoPath.1)",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                    "Opera/9.10 (Windows NT 5.1; U; ru)",
                    "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1; aggregator:Tailrank; http://tailrank.com/robot) Gecko/20021130",
                    "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
                    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
                    "Opera/9.22 (Windows NT 6.0; U; ru)",
                    "Opera/9.22 (Windows NT 6.0; U; ru)",
                    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
                    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; MRSPUTNIK 1, 8, 0, 17 HW; MRA 4.10 (build 01952); .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
                    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",
                    "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9"
                );

                $useragent = $all_useragents[ array_rand( $all_useragents )];

                $i = 0;
                for( $i = 0; $i < count( $urls ); $i = $i + $threads )
                {
                    $urls_pack[] = array_slice( $urls, $i, $threads );
                }
                foreach( $urls_pack as $pack )
                {
                    $mh = curl_multi_init();
                    unset( $conn );
                    foreach ( $pack as $i => $url )
                    {
                        $conn[$i]=curl_init( trim( $url ));
                        curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($conn[$i], CURLOPT_TIMEOUT, $timeout );
                        curl_setopt($conn[$i], CURLOPT_USERAGENT, $useragent );
                        curl_multi_add_handle ( $mh,$conn[ $i ]);
                    }
                    do {
                        $n=curl_multi_exec( $mh,$active );
                        sleep( 0.01 ); }
                    while ( $active );

                    foreach ( $pack as $i => $url )
                    {
                        $result[]=curl_multi_getcontent( $conn[ $i ]);
                        curl_close( $conn[$i] );
                    }
                    curl_multi_close( $mh );
                }
            }

            public static function get_service_access_token( $app_id = self::APP_ID_STATISTICS )
            {
                $at = json_decode(MemcacheHelper::Get( self::MEM_TOKENS_KEY), $toArray = true );
                if ( empty( $at )) {
                    $connect = ConnectionFactory::Get( 'tst' );
                    $sql = 'SELECT *
                            FROM serv_access_tokens
                            WHERE active IS TRUE';
                    $cmd = new SqlCommand( $sql, $connect );
                    $cmd->SetInt( '@app_id', $app_id );
                    $ds  = $cmd->Execute();
                    $at = [];
                    while( $ds->Next()) {
                        $at[] = [
                            'token'         =>  $ds->GetString( 'access_token' ),
                            'updated_at'    =>  microtime(true),
                            'app_id'        =>  $ds->GetInteger('app_id'),
                            'vkId'          =>  $ds->GetInteger('user_id'),
                            'errors'        =>  0
                        ];
                    }
                    MemcacheHelper::Set( self::MEM_TOKENS_KEY, ObjectHelper::ToJSON( $at ));
                    sleep(0.2);

                }
                $tryes = 0;
                $result_token = null;
                if ( empty( $at )) {
                    AuditUtility::CreateEvent('accessTokenDead', 'vkId', -1, 'нету токенов1!');
                    return false;
                }
                while( $tryes < 100 ) {
                    $index_res = null;
                    $now = microtime(true);
                    foreach( $at as $index => &$token ) {
                        if ( $token['app_id'] == $app_id && ( $now - $token['updated_at'] > 0.7 ) ) {
                            $result_token = $token;
                            sleep(0.3);
                            if ( !self::check_at( $token['token'])) {
                                sleep(0.3);
                                $token['updated_at'] = $now;
                                $token['errors'] ++;
                                continue;
                            }
                            break(2);
                        }
                    }

                    sleep(0.1);
                    MemcacheHelper::Set( self::MEM_TOKENS_KEY, ObjectHelper::ToJSON( $at ));
                    $at = json_decode( MemcacheHelper::Get( self::MEM_TOKENS_KEY), $toArray = true);
                    $tryes ++;
                }

                if ( $result_token ) {
                    $at[$index]['updated_at'] = $now;
                    MemcacheHelper::Set( self::MEM_TOKENS_KEY, ObjectHelper::ToJSON( $at ));
                    sleep(0.8);
                    return $result_token['token'];
                }
        //                Logger::Warning('нету токенов!');
        //                AuditUtility::CreateEvent('accessTokenDead', 'vkId', -1, 'нету токенов2!');

                return false;

            }

            public static function get_all_service_tokens()
            {
                $connect =  ConnectionFactory::Get( 'tst' );
                $sql = 'SELECT access_token,user_id  FROM serv_access_tokens';
                $cmd = new SqlCommand( $sql, $connect );
                $ds  = $cmd->Execute();
                $result = array();
                while( $ds->Next()) {
                    $result[$ds->GetInteger('user_id')] = $ds->GetValue('access_token');
                }
                return $result;
            }

            public static function deactivate_at( $access_token )
            {
                if ( !$access_token )
                    $access_token = 0;
                $sql = 'UPDATE serv_access_tokens
                        SET active=false
                        WHERE access_token=@access_token';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get( 'tst' ));
                $cmd->SetString('@access_token', $access_token );
                $cmd->Execute();
            }

            public static function check_at( $access_token )
            {
                $res = self::get_vk_time( $access_token );
                sleep( self::PAUSE );
                if ( isset( $res->error )) {
                    //self::deactivate_at( $access_token );
                    return false;
                }
                return true;
            }

            public static function set_service_at( $user_id, $access_token, $app_id )
            {
                $sql = 'INSERT INTO serv_access_tokens(user_id, access_token, app_id )
                        VALUES( @user_id, @access_token, @app_id )';
                $cmd = new SqlCommand( $sql, ConnectionFactory::Get('tst'));
                $cmd->SetString ( '@access_token ', $access_token );
                $cmd->SetInteger( '@user_id ',      $user_id );
                $cmd->SetInteger( '@app_id',        $app_id );
                $cmd->Execute();
            }

            public static function connect( $link, $cookie = null, $post = null, $includeHeader = true, $returnRedirect = false)
            {
                $ch = curl_init();

                curl_setopt( $ch, CURLOPT_URL, $link );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
                if ($includeHeader) {
                    curl_setopt( $ch, CURLOPT_HEADER, 1 );
                }
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt($ch, CURLOPT_USERAGENT,
                    'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');
                if( $cookie !== null )
                    curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
                if( $post !== null )
                {
                    curl_setopt( $ch, CURLOPT_POST, 1 );
                    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
                }
                $res = curl_exec( $ch );
                if (curl_errno($ch)) {
                    echo "<br>error in curl: ". curl_error($ch) ."<br>";
                    return 'error in curl: '. curl_error($ch);
                }
        //                $headers = curl_getinfo($ch);
                curl_close( $ch );
                return $res;
            }

            public static function vk_authorize( $login = null, $pass = null )
            {
                if( !$login) {
                    shuffle( self::$serv_bots);
                    $login = self::$serv_bots[0]['login'];
                    $pass  = self::$serv_bots[0]['pass'];
                }
                $res = self::connect("http://login.vk.com/?act=login&email=$login&pass=$pass");
                if( preg_match( "/remixsid=(.*?);/", $res, $sid ))
                    return "remixchk=5; remixsid=$sid[1]";
                return false;
            }

            public static function send_alert( $message, $reciever_vk_ids )
            {
                if( !is_array( $reciever_vk_ids )) {
                    $reciever_vk_ids = array( $reciever_vk_ids );
                }
                foreach( $reciever_vk_ids as $vk_id) {
                    $params = array(
                        'uid'           =>   $vk_id,
                        'message'       =>   $message . ' ' . md5(time()) ,
                        'access_token'  =>   self::ALERT_TOKEN,
                    );
                    VkHelper::api_request( 'messages.send', $params );
                    sleep( self::PAUSE );
                }
            }

            public static function captcha( $url )
            {
                //не требующие пока изменений настройки
                $domain="antigate.com";
                $rtimeout = 5;
                $mtimeout = 120;
                $is_phrase = 0;
                $is_regsense = 0;
                $is_numeric = 0;
                $min_len = 0;
                $max_len = 0;
                $is_russian = 1;

                $try_counter = 0;
                while (true) {
                    $try_counter ++;
                    if ($try_counter > self::FALSE_COUNTER)
                        return false;
                    $jp = file_get_contents( $url );
                    file_put_contents('capcha.jpg', $jp);

                    $filename = realpath('capcha.jpg');

                    if (!file_exists($filename))
                    {
                        if (self::TESTING) echo "file $filename not found\n";
                        return false;
                    }
                    $postdata = array(
                        'method'        => 'post',
                        'key'           => self::ANTIGATE_KEY,
                        'file'          => '@' . $filename,
                        'phrase'        => $is_phrase,
                        'regsense'      => $is_regsense,
                        'numeric'       => $is_numeric,
                        'min_len'       => $min_len,
                        'max_len'       => $max_len,

                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,             "http://$domain/in.php");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,     1);
                    curl_setopt($ch, CURLOPT_TIMEOUT,             60);
                    curl_setopt($ch, CURLOPT_POST,                 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,         $postdata);
                    $result = curl_exec($ch);
                    if (curl_errno($ch))
                    {
                        if (self::TESTING) echo "CURL returned error: ".curl_error($ch)."\n";
                        return false;
                    }
                    curl_close($ch);
                    if (strpos($result, "ERROR")!==false) {
                        if (self::TESTING) echo "server returned error: $result\n";
                        return false;
                    } else {
                        $ex = explode("|", $result);
                        $captcha_id = $ex[1];
                        if (self::TESTING) echo "captcha sent, got captcha ID $captcha_id\n";
                        $waittime = 0;
                        if (self::TESTING) echo "waiting for $rtimeout seconds\n";
                        sleep($rtimeout);
                        while(true) {
                            $result = file_get_contents("http://$domain/res.php?key=".self::ANTIGATE_KEY.'&action=get&id='.$captcha_id);
                            if (strpos($result, 'ERROR') !== false) {
                                if (self::TESTING) echo "server returned error: $result\n";
                                continue(2);
                            }
                            if ($result=="CAPCHA_NOT_READY") {
                                if (self::TESTING) echo "captcha is not ready yet\n";
                                $waittime += $rtimeout;
                                if ($waittime>$mtimeout) {
                                    if (self::TESTING) echo "timelimit ($mtimeout) hit\n";
                                    continue(2);
                                }
                                if ( self::TESTING ) echo "waiting for $rtimeout seconds\n";
                                sleep($rtimeout);
                            } else {
                                $ex = explode( '|', $result );
                                if ( trim( $ex[ 0 ] )=='OK' ) return trim($ex[1]);
                            }
                        }
                        return false;
                    }
                }
                return false;
            }

            //сдвигаем на более поздный период все отложенные посты из интервала
            public static function clearVkPostponed( $targetFeed, $fromTs, $toTs, $skipPostIds = [] ) {
                $currentId = AuthVkontakte::IsAuth();
                $tokens = AccessTokenUtility::getAllTokens(
                    $targetFeed->targetFeedId,
                    $checkTokenVersion = false,
                    [UserFeed::ROLE_ADMINISTRATOR, UserFeed::ROLE_OWNER, UserFeed::ROLE_ADMINISTRATOR]);
                if( $currentId ) {
                    $currToken = AccessTokenUtility::getTokens( $currentId, $targetFeed);
                    if( !empty($currToken)) {
                        $tokens[$currentId] = reset($currToken)->accessToken;
                    }
                }

                $params = array(
                    'owner_id'  =>  '-' . $targetFeed->externalId,
                    'filter'    =>  'postponed',
                    'count'     =>  50,
                    'v'         =>  '5.2'
                );
                $postponedPosts  = array();
                foreach( $tokens as $token )  {
                    try {
                        $params['access_token'] = $token;
                        $postponedPosts = VkHelper::api_request( 'wall.get', $params );
                        break;
                    } catch( Exception $e ) {
                    }
                }
                $postsForMove = array();

                // выбираем посты для сдвига, проверяем, свободно ли место под эти посты,если нет - двигаем и эти
                $counter = 0;
                foreach ( $postponedPosts->items as $post ) {
                    if (!isset($post->date) ||
                        !($fromTs <= $post->date && $post->date <= $toTs + $counter * VkHelper::INTERVAL_BETWEEN_MOVED_POSTS ) || //лежит ли в интервале проверки
                        in_array( $post->to_id . '_' . $post->id, $skipPostIds) //нужно ли двигать пост с этим id
                    ) {
                        continue;
                    }

                    $postsForMove[] = $post;
                    $counter ++;
                }

                $postsForMove = array_reverse( $postsForMove );

                if ( !empty($postsForMove )) {
                    $res = VkHelper::movePosts($postsForMove, $tokens, $toTs);
                }
                return;
            }

            //составляет execute код для vk и отправляет его на выполнение. смещает посты на конец периода защиты, с интервалом в 5 минут
            public static function movePosts( $posts, $tokens, $endProtectTs ) {
                $code = '';
                $endProtectTs += VkHelper::INTERVAL_BETWEEN_MOVED_POSTS;
                foreach( $posts as $post ) {
                    $rtsPost = array();
                    $rtsAttachments = array();
                    if (isset( $post->attachments )) {
                        foreach( $post->attachments as $attach) {
                            if ( $attach->type == 'link' ) {
                                $rtsAttachments[] = $attach->link->url;
                                continue;
                            }
                            $type =  $attach->type;
                            $idName = VkHelper::$contentTypeName[$type];
                            $rtsAttachments[] = $attach->type . $attach->$type->owner_id . '_' . $attach->$type->$idName;
                        }
                    }
                    $rtsPost['message']     = $post->text;
                    $rtsPost['post_id']     = $post->id;
                    $rtsPost['owner_id']    = $post->to_id;
                    $rtsPost['from_group']  = 1;
                    $rtsPost['publish_date']= $endProtectTs;
                    if( !empty($rtsAttachments))
                        $rtsPost['attachments'] = implode( ',', $rtsAttachments );
                    $code   .=  'API.wall.edit(' . json_encode( $rtsPost, JSON_UNESCAPED_UNICODE ) . ');';
                    $endProtectTs += VkHelper::INTERVAL_BETWEEN_MOVED_POSTS;
                }

                $res = false;
                foreach( $tokens as $token ) {
                    $params = array('code'=> $code, 'access_token' => $token );
                    try {
                        $res = VkHelper::api_request('execute', $params );
                        break;
                    } catch( Exception $e) {
//                        print_r($e->getMessage());
                    }
                }
                return (bool)$res;
            }
        }
?>