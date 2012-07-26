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
            self::Source => 'Источники',
            self::Ads => 'Реклама',
        );

        public static function IsTopFeed(SourceFeed $sourceFeed) {
            return in_array($sourceFeed->externalId, self::$Tops);
        }

        public static function GetInfo($sourceFeeds, $key = 'sourceFeedId') {
            $sourceInfo = array();

            foreach ($sourceFeeds as $sourceFeed) {
                $sourceInfo[$sourceFeed->$key] = array(
                    'name' => $sourceFeed->title,
                    'img' => ''
                );

                //group image
                $path = 'temp://userpic-' . $sourceFeed->externalId . '.jpg';
                if (!file_exists(Site::GetRealPath($path))) {
                    $path = 'images://fe/no-avatar.png';
                } else {
                    $path .= '?v=' . filemtime(Site::GetRealPath($path));
                }

                $sourceInfo[$sourceFeed->$key]['img'] = Site::GetWebPath($path);
            }

            return $sourceInfo;
        }

        public static function SaveRemoteImage($externalId) {
            $path = 'temp://userpic-' . $externalId . '.jpg';
            $filePath = Site::GetRealPath($path);
            try {
                $parser = new ParserVkontakte();
                $info = $parser->get_info(ParserVkontakte::VK_URL . '/public' . $externalId);

                if (!empty($info['avatarа'])) {
                    $avatarPath = $info['avatarа'];
                    $content = file_get_contents($avatarPath);
                    if (!empty($content)) {
                        file_put_contents($filePath, $content);
                    }
                }
            } catch (Exception $Ex) {}
        }

        public static function GetAll() {
            $sourceFeeds = SourceFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            $sourceFeed = new SourceFeed();
            $sourceFeed->sourceFeedId = -1;
            $sourceFeed->title = 'Авторские';
            $sourceFeeds = array(-1 => $sourceFeed) + $sourceFeeds;
            return $sourceFeeds;
        }
    }
?>