<?php
    /**
     * SourceFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class SourceFeedUtility {
        const Source = 'source';

        const Ads = 'ads';

        public static $Types = array(
            self::Source => 'Источник',
            self::Ads => 'Рекламная лента',
        );
    }
?>