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

        const FakeSourceRepost  = -3;

        public static $Types = array(
            self::Source => 'Источники',
            self::Ads => 'Реклама',
            self::My => 'Мои публикации',
            self::Authors => 'Заметки',
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

        public static function SaveRemoteImage( $externalIds ) {
            if( !is_array( $externalIds))
                $externalIds = array($externalIds);
            $externalIdsChunks = array_chunk( $externalIds, 300 );
            foreach( $externalIdsChunks as $chunk ) {
                try {
                    $feedsVkInfo = StatPublics::get_publics_info( $chunk );
                    foreach( $feedsVkInfo as $feedInfo ) {
                        self::DownloadImage( $feedInfo['id'], $feedInfo['ava'] );
                    }
                } catch (Exception $Ex) {}
            }
        }

        public static function UpdateFeedInfo( $feeds ) {
            if( $feeds ) {
                $ids = array_keys( $feeds );
                try {
                    $feedsVkInfo = StatPublics::get_publics_info( $ids );
                    foreach( $feedsVkInfo as $feedInfo ) {
                        self::DownloadImage( $feedInfo['id'], $feedInfo['ava'] );
                        $feeds[$feedInfo['id']]->title = $feedInfo['name'];
                    }
                } catch (Exception $Ex) {}
            }
        }

        public static function DownloadImage( $sourceExternalId, $imgUrl ) {
            $path = 'temp://userpic-' . $sourceExternalId. '.jpg';
            $filePath = Site::GetRealPath($path);
            $content = file_get_contents( $imgUrl );
            if (!empty($content)) {
                file_put_contents($filePath, $content);
                Logger::Debug( $imgUrl  . ' -> ' . Site::GetWebPath($path));
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
                    self::FakeSourceRepost  => $sourceFeedTopface,
                ) + $sourceFeeds;
            return $sourceFeeds;
        }

        public static function SaveSourceInfo( SourceFeed $source) {
            if( !$source->externalId)
                return false;

            $info = reset( StatPublics::get_publics_info( $source->externalId ));
            $source->title = $info['name'];
            self::DownloadImage( $source->externalId, $info['ava'] );

        }
    }
?>