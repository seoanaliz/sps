<?php
/**
 * 474993341.08c5c6e.98d7f8e316574e7aa5452112007f6d03
 * https://instagram.com/oauth/authorize/?client_id=08c5c6e715ed4299bd336fe268bdd888&redirect_uri=http://www.akalie.0fees.net/insta.php&response_type=token
 */
class InstagramHelper
{
    const INST_API_URL  = 'https://api.instagram.com/v1/';
    const TOKEN         = '474993341.08c5c6e.98d7f8e316574e7aa5452112007f6d03';
    const SUCCESS_CODE  =  200;

    public static function qurl_request( $url, $arr_of_fields, $post = false, $headers = '', $uagent = '')
    {
        if (empty( $url )) {
            return false;
        }

        if( !$post ) {
            $url .= '?' . http_build_query($arr_of_fields);
        }
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT , 6 );

        if( $post ) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if (is_array( $arr_of_fields )) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_of_fields));

            } else return false;
        }
        if (is_array( $headers )) { // если заданы какие-то заголовки для браузера
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($uagent)) { // если задан UserAgent
            curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        } else{
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1)');
        }


        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "<br>error in curl: ". curl_error($ch) ."<br>";
            return 'error in curl: '. curl_error($ch);
        }

        curl_close($ch);
        return $result;
    }

    public static function api_request( $method, $params ) {
        $url = self::INST_API_URL . $method;
        if( !isset( $params['access_token'])) {
            $params['access_token'] = self::TOKEN;
        }
        $res = self::qurl_request( $url, $params);
        $res = json_decode( $res );
        if( $res->meta->code != self::SUCCESS_CODE ) {
            throw new Exception( 'Error in ' . $method . ' on params ' . json_encode($params) .
                                 ', error: ' . json_encode( $res ));
        } else {
            return $res->data;
        }
    }
}
