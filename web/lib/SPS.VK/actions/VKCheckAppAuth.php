<?php
    Package::Load( 'SPS.VK' );

    /**
     * VKCheckAppAuth Action
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class VKCheckAppAuth {

        public function Execute() {
            $api_id     = Request::getInteger('api_id');
            $viewer_id  = Request::getInteger('viewer_id');
            $secret     = 'X1zsnZdfoL1ywzRODpEg';
            $auth_key   = Request::getString('auth_key');
            $auth_key_trust = md5($api_id . '_' . $viewer_id . '_' . $secret);

            if ($auth_key != $auth_key_trust) {
                return 'empty';
            }

            //TODO проверить, имеет ли чувак вообще доступ

            Session::setInteger('vk_user_id', $viewer_id);
        }
    }
?>