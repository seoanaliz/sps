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

        const Authors = 'authors';

        const Topface = 'topface';

        const Albums = 'albums';

        const AuthorsList = 'authors-list';

        const My = 'my';

        const FakeSourceAuthors = -1;

        const FakeSourceTopface = -2;
        /**
         * посты не из socialbord'а
         */
        const FakeSourceNotSbPosts = -3;

        public static $Types = array(
            self::Source => 'Источники',
            self::Ads => 'Реклама',
            self::My => 'Мои публикации',
            self::Authors => 'Авторские',
            self::Albums => 'Альбомы',
            self::Topface => 'Topface',
            self::AuthorsList => '+',
        );

        public static function IsTopFeed($sourceFeed) {
            if (empty($sourceFeed->externalId)) {
                return false;
            } else {
                return in_array($sourceFeed->externalId, self::$Tops);
            }
        }

        public static function GetInfo($sourceFeeds, $key = 'sourceFeedId') {
            $sourceInfo = array();

            if (!empty($sourceFeeds)) {
                foreach ($sourceFeeds as $sourceFeed) {
                    if (empty($sourceFeed->$key)) continue;
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
            }

            return $sourceInfo;
        }

        public static function SaveRemoteImage($externalIds) {
            if( !is_array( $externalIds))
                $externalIds = array($externalIds);
            $externalIdsChunks = array_chunk( $externalIds, 300 );
            foreach( $externalIdsChunks as $chunk ) {
                try {
                    $feedsVkInfo = StatPublics::get_publics_info( $chunk );
                    foreach( $feedsVkInfo as $feedInfo ) {
                        $path = 'temp://userpic-' . $feedInfo['id'] . '.jpg';
                        $filePath = Site::GetRealPath($path);
                        $content = file_get_contents($feedInfo['ava']);
                        if (!empty($content)) {
                            file_put_contents($filePath, $content);
                            Logger::Debug($feedInfo['ava'] . ' -> ' . Site::GetWebPath($path));
                        }
                    }
                } catch (Exception $Ex) {}
            }
        }

        public static function GetAll() {
            $sourceFeeds = SourceFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            $sourceFeedAuthors = new SourceFeed();
            $sourceFeedAuthors->sourceFeedId = self::FakeSourceAuthors;
            $sourceFeedAuthors->title = 'Авторские';

            $sourceFeedTopface = new SourceFeed();
            $sourceFeedTopface->sourceFeedId = self::FakeSourceTopface;
            $sourceFeedTopface->title = 'Topface';

            $sourceFeeds = 
                array(
                    self::FakeSourceAuthors => $sourceFeedAuthors,
                    self::FakeSourceTopface => $sourceFeedTopface,
                ) + $sourceFeeds;
            return $sourceFeeds;
        }
    }
?>
