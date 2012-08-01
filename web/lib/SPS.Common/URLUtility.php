<?php
    /**
     * URLUtility
     * @author Shuler
     * @package H4U.System
     */
    class URLUtility {

        /**
         * Check URL
         *
         * @static
         * @param $url
         * @return bool
         */
        public static function CheckUrl( $url ) {
            $curlHandle = curl_init();
            curl_setopt( $curlHandle, CURLOPT_TIMEOUT, 15 );
            curl_setopt( $curlHandle, CURLOPT_URL, $url );
            curl_setopt( $curlHandle, CURLOPT_FAILONERROR, 1 );
            curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $curlHandle, CURLOPT_CONNECTTIMEOUT, 10 );
            curl_setopt( $curlHandle, CURLOPT_NOBODY, true );
            curl_setopt( $curlHandle, CURLOPT_VERBOSE, false );
            curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, false );

            $result = curl_exec( $curlHandle );
            $err = curl_error( $curlHandle );
            if ( !empty( $err ) ) {
                return false;
            }

            $httpcode = curl_getinfo( $curlHandle, CURLINFO_HTTP_CODE );
            curl_close( $curlHandle );
            return ( $httpcode == 200 );
        }
    }
?>