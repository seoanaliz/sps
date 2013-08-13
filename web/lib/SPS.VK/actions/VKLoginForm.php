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
            Response::setString('href', AuthVkontakte::makeVkLoginLink(Request::getString('to')));
        }
    }
?>