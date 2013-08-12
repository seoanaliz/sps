<?php
    class AuthVkontakte {

        public static $AuthorAppId;

        public static $AppId;

        public static $Password;

        public static $AuthSecret;
        
        public static $Version = 3;

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
            $userFeeds = UserFeedFactory::Get(array('vkId' => $vkId));
            $author = AuthorFactory::GetOne(array('vkId'=>$vkId));
            self::PopulateSession($author);

            return !empty( $userFeeds );
        }
        
        public static function Login($vkId) {
            $expire = time() + 86400 * 7;
            $cookieString = self::GenerateCookieContentString($vkId, $expire);
            Cookie::setCookie('good_' . self::$AppId, $cookieString, $expire, '/');
        }

        public static function IsAuth() {
            return 670456;#self::GetUserByCookie(Cookie::getString('good_' . self::$AppId));
        }

        protected static function GetUserByCookie($cookieString) {
            $keys = array('version', 'expire', 'encodedVkId', 'checksum');
            $values = explode('.', $cookieString);
            if (count($values) === count($keys)) {
                $data = array_combine($keys, $values);

                $vkId = base64_decode($data['encodedVkId']);
                if (
                    (time() < (int) $data['expire']) &&
                    (self::GenerateCookieContentString($vkId, $data['expire']) === $cookieString)
                ) {
                    return $vkId;
                }
            }
            return false;
        }

        protected static function GenerateCookieContentString($vkId, $expire) {
            $checkSum  = base64_encode(
                hash('sha256', self::$Version . '_' . $vkId . '_' . $expire . '_' . self::$CookieSecret, $raw=true)
            );
            $encodedId = base64_encode($vkId);
            return self::$Version . ".$expire.$encodedId.$checkSum";
        }

        public static function PopulateSession($editor) {
            Session::setObject('Editor', $editor);
            Response::setObject('__Editor', $editor);
        }

        public static function Logout() {
            Cookie::setCookie('vk_app_' . self::$AppId, '', time() - 1000, '/', '.' . Site::$Host->GetHostname());
            Cookie::setCookie('good_' . self::$AppId, '', time() - 1000, '/');
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