<?php
    class AuthVkontakte {

        public static $AppId;

        public static $Password;

        public static $AuthSecret;

        public static function Init( DOMNodeList $params ) {
            foreach ( $params as $param ) {
                $name   = $param->getAttribute( 'name' );
                $value  = $param->nodeValue;

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

                    self::Login($cookie_data['mid']);

                    return $cookie_data['mid'];
                }
            }

            return false;
        }

        private static function Login($vkId) {
            $editor = EditorFactory::GetOne(
                array('vkId' => $vkId)
            );

            Session::setObject('Editor', $editor);
            Response::setObject('__Editor', $editor);
        }

        /**
         * Производит разлогинивание
         */
        public static function Logout() {
            Cookie::setCookie(  'vk_app_' . self::$AppId,    "", time() - 1024, '/' );
            Cookie::setCookie(  'vk_app_trust' . self::$AppId, "", time() - 1024, '/' );
        }
    }
?>