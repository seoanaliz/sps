<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKCheckAuth Action
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class VKCheckAuth {

        public function Execute() {
            $checkEditor = Request::getBoolean( 'checkEditor' );

            $vk_auth = AuthVkontakte::IsAuth($checkEditor);
            if ($vk_auth === false) {
                if (!$checkEditor) {
                    return 'login';
                } else {
                    return 'empty';
                }
            }
        }
    }
?>