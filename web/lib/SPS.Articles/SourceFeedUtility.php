<?php
    /**
     * SourceFeedUtility
     * @package    SPS
     * @subpackage Articles
     * @author     Shuler
     */
    class SourceFeedUtility {
        const TOP_FEMALE = 'top-female';
        const TOP_MALE = 'top-male';

        public static $Tops = array(self::TOP_FEMALE, self::TOP_MALE);

        const Source = 'source';

        const Ads = 'ads';

        public static $Types = array(
            self::Source => 'Источник',
            self::Ads => 'Рекламная лента',
        );

        public static function IsTopFeed(SourceFeed $sourceFeed) {
            return in_array($sourceFeed->externalId, self::$Tops);
        }
    }
?>