<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKLoginRedirect Action
     * @package    SPS
     * @subpackage VK
     * @author     Eugene Kulikov
     */
    class VKLoginRedirect {

        public function Execute() {
            $code = Request::getString('code');
            $redirectUrl = Request::getString('to') ?: '/';
            if ($code) {
                $protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
                $tokenGetUrl = 'https://oauth.vk.com/access_token'.
                    '?client_id='. AuthVkontakte::$AppId .
                    '&client_secret='. AuthVkontakte::$Password .
                    '&code='. $code .
                    '&redirect_uri=' . urlencode($protocol . '://' . trim(Request::GetHTTPHost(), '/') . '/vk-login/?to=' . $redirectUrl);
                $answer = json_decode(
                    VkHelper::connect($tokenGetUrl, $setCookie = null, $usePost = null, $includeHeaderInOutput = false),
                    $array=true
                );
                if (isset($answer['user_id'])) {
                    $loginResult = AuthVkontakte::Login($answer['user_id']);
                    if (!$loginResult) {
                        $redirectUrl = '/stat/';
                    }
                }
            }

            Response::SetString('redirect', $redirectUrl);
            return 'redirect';
        }
    }
?>