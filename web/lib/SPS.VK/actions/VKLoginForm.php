<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKLoginForm Action
     * @package    SPS
     * @subpackage VK
     * @author     Eugene Kulikov
     */
    class VKLoginForm {

        public function Execute() {
            $vkRedirectUrl = urlencode(
                AuthVkontakte::getSiteUrl() . '/vk-login/?to=' . Request::getString('to')
            );
            $vkHref = 'https://oauth.vk.com/authorize?'.
                    'client_id='. AuthVkontakte::$AppId .
                    '&scope=stats,groups,offline'.
                    '&redirect_uri='. $vkRedirectUrl .
                    '&display=page'.
                    '&response_type=code';

            Response::setString('href', $vkHref);
        }
    }
?>