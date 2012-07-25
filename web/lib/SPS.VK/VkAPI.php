<?php
    /**
     * VkAPI
     * @package    SPS
     * @subpackage Vk
     * @author     Shuler
     */
    class VkAPI {

        /**
         * @var VKontakte
         */
        private static $instance = null;

        public static function GetInstance() {
            if (empty(self::$instance)) {
                $vkontakte = new VKontakte();
                $vkontakte->setApiUrl('http://api.vk.com/api.php');
                $vkontakte->setApiId(AuthVkontakte::$AppId);
                $vkontakte->setSecret(AuthVkontakte::$Password);

                self::$instance = $vkontakte;
            }

            return self::$instance;
        }
    }
?>