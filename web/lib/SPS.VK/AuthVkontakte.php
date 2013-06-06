<?php
    class AuthVkontakte {

        public static $AuthorAppId;

        public static $AppId;

        public static $Password;

        public static $AuthSecret;

        protected static $CookieSecret = 't2MJebh87ZmYdN2i2btAXGLv+Z1NxrYcA4AgHNQMYvM=';

        public static function Init( DOMNodeList $params ) {
            foreach ( $params as $param ) {
                $name   = $param->getAttribute( 'name' );
                $value  = $param->nodeValue;

                if ($name == 'authorAppId') self::$AuthorAppId = $value;
                if ($name == 'appId') self::$AppId = $value;
                if ($name == 'password') self::$Password = $value;
                if ($name == 'authSecret') self::$AuthSecret = $value;
            }
        }

        public static function IsEditor($vkId) {
            $editor = EditorFactory::GetOne(
                array('vkId' => $vkId)
            );
            self::PopulateSession($editor);

            return (bool) $editor;
        }
        
        public static function Login($vkId) {
            $expire = time() + 86400 * 7;
            $cookieString = self::GenerateCookieContentString($vkId, $expire);
            Cookie::setCookie('good_' . self::$AppId, $cookieString, $expire, '/');
        }

        public static function IsAuth() {
            return self::GetUserByCookie(Cookie::getString('good_' . self::$AppId));
        }

        protected static function GetUserByCookie($cookieString) {
            $keys = array('version', 'expire', 'encodedVkId', 'checksum');
            $data = explode('.', $cookieString);
            if (count($data) === count($keys)) {
                $cookieData = array_combine($keys, $data);
                $vkId = base64_decode($cookieData['encodedVkId']);
                if (
                    (time() < (int) $cookieData['expire']) &&
                    (self::GenerateCookieContentString($vkId, $cookieData['expire']) === $cookieString)
                ) {
                    return $vkId;
                }
            }
            return false;
        }

        protected static function GenerateCookieContentString($vkId, $expire) {
            $checkSum  = base64_encode(
                hash('sha256', $vkId . '_' . $expire . '_' . self::$CookieSecret, $raw=true)
            );
            $version = 1;
            $encodedId = base64_encode($vkId);
            return "$version.$expire.$encodedId.$checkSum";
        }

        public static function PopulateSession($editor) {
            Session::setObject('Editor', $editor);
            Response::setObject('__Editor', $editor);
        }

        public static function Logout() {
            Cookie::setCookie( 'vk_app_' . self::$AppId,    "", time() - 1024, '/', '.' . Site::$Host->GetHostname);
            Cookie::setCookie('good_' . self::$AppId, '', time() - 1024, '/');
            self::PopulateSession(null);
        }

        public static function getSiteUrl() {
            $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
            $isNonStandardPort = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
            $port = $isNonStandardPort ? ':'.$_SERVER["SERVER_PORT"] : '';
            return ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port;
        }
    }
?>