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

        public static function Login($vkId) {
            $editor = EditorFactory::GetOne(
                array('vkId' => $vkId)
            );
            self::PopulateSession($editor);

            if ($editor) {
                $expire = time() + 86400 * 7;
                $cookieString = self::GenerateCookieContentString($editor->editorId, $vkId, $expire);
                Cookie::setCookie('good_' . self::$AppId, $cookieString, $expire, '/');
                return true;
            }
            return false;
        }

        public static function IsAuth() {
            return self::GetUserByCookie(Cookie::getString('good_' . self::$AppId));
        }

        protected static function GetUserByCookie($cookieString) {
            $keys = array('version', 'expire', 'uid', 'checksum');
            $data = explode('.', $cookieString);
            if (count($data) === count($keys)) {
                $cookieData = array_combine($keys, $data);
                $editor = EditorFactory::GetOne(
                    array('editorId' => (int) $cookieData['uid'])
                );
                if ($editor &&
                    (time() < (int) $cookieData['expire']) &&
                    (self::GenerateCookieContentString($cookieData['uid'], $editor->vkId, $cookieData['expire']) === $cookieString)
                ) {
                    return $editor->vkId;
                }
            }
            return false;
        }

        protected static function GenerateCookieContentString($userId, $vkId, $expire) {
            $checkSum  = base64_encode(
                hash('sha256', $vkId . '_' . $expire . '_' . self::$CookieSecret, $raw=true)
            );
            $version = 1;
            return "$version.$expire.$userId.$checkSum";
        }


        public static function PopulateSession($editor) {
            Session::setObject('Editor', $editor);
            Response::setObject('__Editor', $editor);
        }

        public static function Logout() {
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