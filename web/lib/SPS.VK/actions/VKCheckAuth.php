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
            $vkId = AuthVkontakte::IsAuth();
            if ($vkId) {
                AuthVkontakte::PopulateSession(EditorFactory::GetOne(
                    array('vkId' => $vkId)
                ));
            } else {
                Response::SetString('redirect', Request::getRequestUri());
                return 'login';
            }
        }
    }
?>