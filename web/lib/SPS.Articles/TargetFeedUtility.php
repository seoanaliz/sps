<?php
    /**
     * TargetFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class TargetFeedUtility {
        const VK = 'vk';

        const FB = 'fb';

        const VK_ALBUM = 'vk_album';

        public static $Types = array(
            self::VK => 'ВКонтакте',
            self::FB => 'Facebook',
            self::VK_ALBUM => 'альбом вк',
        );
    }

?>