<?php
    /**
     * Array Helper
     * @package SPS
     * @subpackage Stat
     */

//    define ( 'ACC_TOK_WRK', 'b03d241fb0371ee7b0371ee7b6b01c4063bb037b0222679cb604e99dfff088b' );
    define ( 'ACC_TOK_WRK', '0b8c8e800086894200868942b100a9af1a000860093b1dc50eb180b9b836874e8ec5f99' );
    define ( 'VK_API_URL' , 'https://api.vk.com/method/' );

    class VkHelper {

        /**
         *wrap for VkAPI
         *
         * @static
         * @return array
         */

        const TESTING = false;
        public static function api_request( $method, $request_params, $throw_exc_on_errors = 1 )
        {
            if ( !isset( $request_params['access_token'] ))
                $request_params['access_token']  =  ACC_TOK_WRK;
            $url = VK_API_URL . $method;
            $res = json_decode( VkHelper::qurl_request( $url, $request_params ) );
            if ( isset( $res->error ) )
                if ( $throw_exc_on_errors ) {
                    throw new Exception('Error : ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ) );
                }
                else
                    return $res;

            return $res->response;
        }

        public static function qurl_request( $url, $arr_of_fields, $headers = '', $uagent = '')
        {
            if (empty( $url )) {
                return false;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            if (is_array($headers)) { // если заданы какие-то заголовки для браузера
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
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_of_fields);

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                echo "<br>error in curl: ". curl_error($ch) ."<br>";
                return 'error in curl: '. curl_error($ch);
            }

            curl_close($ch);
            return $result;
        }

        public static function get_vk_time()
        {
            return self::api_request( 'getServerTime', array( 'access_token' =>  '' ), 0 );
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

        public static function get_service_access_token()
        {
            return 'ac76f4c1ac7cce39ac7cce396eac53e861aac7cac69f6a7fc0316a798a4d74a14f6e2e6';
        }
    }
?>