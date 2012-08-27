<?php
    /**
     * Array Helper
     * @package SPS
     * @subpackage Stat
     */

//    define ( 'ACC_TOK_WRK', 'b03d241fb0371ee7b0371ee7b6b01c4063bb037b0222679cb604e99dfff088b' );
    define ( 'ACC_TOK_WRK', '35b9bd2b3dbdfebd3dbdfebd6e3d96a03933dbd3db8c62b879c7877d660642a' );
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
            if ( !isset( $request_params['access_token'] ) )
                $request_params['access_token']  =  ACC_TOK_WRK;
            $url = VK_API_URL . $method;
            $res = json_decode( VkHelper::qurl_request( $url, $request_params ) );

            if ( isset( $res->error ) )
                if ( $throw_exc_on_errors ) {
                   // print_r('Error : ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ) );
                    throw new Exception( 'Error : ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ) );
                }
                else
                    return $res;

            return $res->response;
        }

        public static function qurl_request($url, $arr_of_fields, $headers = '', $uagent = '')
        {
            if (empty($url)) {
                return false;
            }
            if (self::TESTING) {
                echo '<br>данные для запроса <br>';
                print_r($arr_of_fields);
                echo '<br>';
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
            if (is_array($arr_of_fields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_of_fields);

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                echo "<br>error in curl: ". curl_error($ch) ."<br>";
                return 'error in curl: '. curl_error($ch);
            }

            if (self::TESTING) {
                echo '<br>ответ <br>';
                print_r($result);
                echo '<br>';
            }
            curl_close($ch);
            return $result;
        }

        public static function get_stripped_text( $text ) {

            $text = preg_replace('/<.?span.*?>/', '', $text);
            $text = preg_replace('/<a.*?>/', '', $text);
            $text = str_replace('<br>', ' ', $text);

            $text = strip_tags( $text );
            $text = pg_escape_string( $text );
            $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
            $text = html_entity_decode( $text, ENT_QUOTES, 'cp1251' );
            $text = htmlspecialchars_decode( $text );

            return $text;
        }

    }

?>