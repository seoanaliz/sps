<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKLogoutRedirect Action
     * @package    SPS
     * @subpackage VK
     * @author     Eugene Kulikov
     */
    class VKLogoutRedirect {

        public function Execute() {
            AuthVkontakte::Logout();
            Response::SetString('redirect', Request::getString('to') ?: '/');
            return 'redirect';
        }
    }
?>