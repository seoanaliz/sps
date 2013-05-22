<?php
    /**
     * TargetFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class TargetFeedUtility {
        const VK = 'vk';

        const VK_ALBUM = 'vk_album';

        const FB = 'fb';

        public static $Types = array(
            self::VK => 'ВКонтакте',
            self::FB => 'Facebook',
            self::VK_ALBUM => 'Вконтакте_альбомы'
        );
    }

?>