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


            /**
             *id аппа статистки
             */
            const APP_ID_STATISTICS = 2642172;
            const ALERT_TOKEN = "9a52c2c5ad3c3a0dba10d682cd5e70e99aea7ca665701c2f754fb94e33775cf842485db7b5ec5fb49b2d5";


            /**
             *id аппа обмена
             */
            const APP_ID_BARTER = 3391730;
            const PAUSE   = 0.5;
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

            public static  $open_methods = array(
                'wall.get'          => true,
                'groups.getById'    => true,
                'wall.getById'      => true,

            );

            public static function api_request( $method, $request_params, $throw_exc_on_errors = 1, $app = '' )
            {
                $app_id = $app == 'barter' ? self::APP_ID_BARTER : self::APP_ID_STATISTICS;
                if ( !isset( $request_params['access_token']) && !isset( self::$open_methods[ $method ]))
                    $request_params['access_token']  =  self::get_service_access_token( $app_id );
                $url = VK_API_URL . $method;
                $a = VkHelper::qurl_request( $url, $request_params );
//                if ( $method == 'stats.get') {
//                    $start = strpos( $a, ',"sex"');
//                    $a = substr_replace( $a, '}]}', $start );
//                }
                $res = json_decode(  $a );
                if( !$res )
                    return array();
                if ( isset( $res->error ) )
                    if ( $throw_exc_on_errors ) {
                        if( $res->error->error_code == 5 )
                            throw new AccessTokenIsDead();
                        else
                            throw new Exception('Error : ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ) );
                    } else {
                        return $res;
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
                return self::api_request( 'getServerTime', array( 'access_token' =>  $access_token ), 0 );
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
                $connect =  ConnectionFactory::Get( 'tst' );
                $count = 0;
                while( 1 ) {
                    if( $count++ > 1000 )
                        throw new Exception ('закончились сервисные токены!');
                    $sql = 'SELECT access_token
                            FROM serv_access_tokens
                            WHERE active IS TRUE
                            AND app_id = @app_id
                            ORDER BY random()
                            LIMIT 1';
                    $cmd = new SqlCommand( $sql, $connect );
                    $cmd->SetInt( '@app_id', $app_id );
                    $ds  = $cmd->Execute();
                    $ds->Next();
                    $at  = $ds->GetString( 'access_token' );
                    if ( !$at ) {
                        throw new Exception ('закончились сервисные токены!');
                    }
                    if ( self::check_at( $at ))
                        return $at;
                }
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

            public static function connect( $link, $cookie=null, $post=null, $includeHeader = true) {
                $ch = curl_init();

                curl_setopt( $ch, CURLOPT_URL, $link );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 0 );
                if ($includeHeader) {
                    curl_setopt( $ch, CURLOPT_HEADER, 1 );
                }
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
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
                if( !preg_match("/hash=([a-z0-9]{1,32})/", $res, $hash )) {
                    return false;
                }
                $res = self::connect("http://vk.com/login.php?act=slogin&hash=" . $hash[1] );
                if( preg_match( "/remixsid=(.*?);/", $res, $sid ))
                    return "remixchk=5; remixsid=$sid[1]";
                return false;
            }

            public static function send_alert( $message, $reciever_vk_ids ) {
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
        }
    ?>