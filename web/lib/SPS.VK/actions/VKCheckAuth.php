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
            Response::SetString('redirect', Request::getRequestUri());
            $vkId = AuthVkontakte::IsAuth();
            if ($vkId === false) {
                return 'login';
            } else if (Request::getBoolean('checkEditor')) {

                $userFeeds = UserFeedFactory::Get(array('vkId' => $vkId));
                if( empty( $userFeeds )) {
                    return 'stat';
                }
//                if (!AuthVkontakte::IsEditor($vkId)) {
//                    return 'stat';
//                }
            }
        }
    }
?>