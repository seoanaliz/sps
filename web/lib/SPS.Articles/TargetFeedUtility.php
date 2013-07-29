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

        const vk_album = 'vk_album';

        public static $Types = array(
            self::VK        => 'ВКонтакте',
            self::FB        => 'Facebook',
            self::vk_album  => 'VK альбом',
        );
    }

?>