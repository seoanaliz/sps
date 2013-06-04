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

        /**
         * Проверяет, залогинен пользователь. Если да - возвращает его ID ВКонтакте, в противном случае - false.
         * @return mixed
         */
        public static function IsAuth() {
            $vk_cookie = Cookie::getString('vk_app_trust' . self::$AppId);
            if (empty($vk_cookie)) {
                if (!isset($_COOKIE['vk_app_' . self::$AppId]))
                    return false;

                $vk_cookie = $_COOKIE['vk_app_' . self::$AppId];
            }

            if (!empty($vk_cookie)) {
                $cookie_data = array();

                foreach (explode('&', $vk_cookie) as $item) {
                    $item_data = explode('=', $item);
                    $cookie_data[$item_data[0]] = $item_data[1];
                }

                // Проверяем sig
                $string = sprintf("expire=%smid=%ssecret=%ssid=%s%s", $cookie_data['expire'], $cookie_data['mid'], $cookie_data['secret'], $cookie_data['sid'], self::$Password);

                if (md5($string) == $cookie_data['sig']) {
                    // sig не подделан - возвращаем ID пользователя ВКонтакте.
                    // авторизуем пользователя совсем надолго
                    $cookie_data['expire'] = time() + 86400 * 7;
                    $cookie_data['sig'] = md5(sprintf("expire=%smid=%ssecret=%ssid=%s%s", $cookie_data['expire'], $cookie_data['mid'], $cookie_data['secret'], $cookie_data['sid'], self::$Password));

                    $newCookie = '';
                    foreach ($cookie_data as $key => $value) {
                        $newCookie .= "&$key=$value";
                    }
                    $newCookie = trim($newCookie, '&');

                    Cookie::setCookie('vk_app_trust' . self::$AppId, $newCookie, $cookie_data['expire'], '/');

                    return $cookie_data['mid'];
                }
            }

            return false;
        }

        public static function Login($vkId) {
            $editor = EditorFactory::GetOne(
                array('vkId' => $vkId)
            );

            Session::setObject('Editor', $editor);
            Response::setObject('__Editor', $editor);

            return !empty($editor);
        }

        /**
         * Производит разлогинивание
         */
        public static function Logout() {
            Cookie::setCookie( 'vk_app_' . self::$AppId,    "", time() - 1024, '/', '.' . Site::$Host->GetHostname() );
            Cookie::setCookie( 'vk_app_trust' . self::$AppId, "", time() - 1024, '/' );
            Session::setObject('Editor', null);
            Response::setObject('__Editor', null);
        }

        public static function LoginAlternative($vkId) {
            $editor = EditorFactory::GetOne(
                array('vkId' => $vkId)
            );
            if ($editor) {
                $expire = time() + 86400 * 7;
                $cookieString = self::GenerateCookieContentString($editor->editorId, $vkId, $expire);
                Cookie::setCookie('good_' . self::$AppId, $cookieString, $expire, '/');
            }
        }

        public static function IsAuthAlternative() {
            return self::IsCookieValid(Cookie::getString('good_' . self::$AppId));
        }

        public static function IsCookieValid($cookieString) {
            $keys = array('version', 'expire', 'uid', 'checksum');
            $data = explode('.', $cookieString);
            if (count($data) === count($keys)) {
                $cookieData = array_combine($keys, $data);
                $editor = EditorFactory::GetOne(
                    array('editorId' => (int) $cookieData['uid'])
                );
                if ($editor && (self::GenerateCookieContentString($cookieData['uid'], $editor->vkId, $cookieData['expire']) === $cookieString)) {
                    return true;
                }
            }
            return false;
        }

        public static function GenerateCookieContentString($userId, $vkId, $expire) {
            $checkSum  = base64_encode(
                hash('sha256', $vkId . '_' . $expire . '_' . self::$CookieSecret, $raw=true)
            );
            $version = 1;
            return "$version.$expire.$userId.$checkSum";
        }

        public static function LogoutAlternative() {
            Cookie::setCookie('good_' . self::$AppId, '', time() - 1000, '/');
        }
    }
?>