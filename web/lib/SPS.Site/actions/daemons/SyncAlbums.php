<?php

Package::Load('SPS.Articles');
Package::Load('SPS.Site');
Package::Load('SPS.VK');
include_once('AbstractPostLoadDaemon.php');

/**
 * SyncSources Action
 * @package    SPS
 * @subpackage Site
 * @author     pavlenko.roman.spb@gmail.com
 */
class SyncAlbums extends AbstractPostLoadDaemon {

    const   PHOTO_COUNT_PER_REQUEST = 20,
            MODE_ALBUMS = 'albums',
            MODE_PHOTO  = 'photo';

    private $skipTargetFeedIds = array( 26 );
    /**
     * Один вызов этого метода просинхронизирует только одну страницу каждого sourceFeed
     */
    public function Execute()
    {
        set_time_limit(0);
        Logger::LogLevel(ELOG_DEBUG);

        $this->daemon = new Daemon();
        $this->daemon->package = 'SPS.Site';
        $this->daemon->method = 'SyncAlbums';
        $this->daemon->maxExecutionTime = '01:00:00';
        if (array_key_exists(self::MODE_ALBUMS, $_REQUEST)) {
            $this->getAlbums();
        } else {
        
            $this->getAlbumPhotos();
        }

    }

    public function getAlbumPhotos()
    {
        //get sources
        $sources = SourceFeedFactory::Get(array('type' => SourceFeedUtility::Albums));

        foreach ($sources as $source) {
            //пропускаем специальные источники
            if (SourceFeedUtility::IsTopFeed($source) || $source->externalId == '-') {
                continue;
            }

            list($public_id, $album_id) = explode('_', $source->externalId);

            //инитим парсер
            $parser = new ParserVkontakte();
            $offset = $source->processed * self::PHOTO_COUNT_PER_REQUEST;
            try {
                $posts = $parser->get_album_as_posts($public_id, $album_id, self::PHOTO_COUNT_PER_REQUEST, $offset);
            } catch (AlbumEndException $exception) {
                Logger::Info('Album ' . $source->externalId . '  end at ' . $offset . ' offset');
                continue;
            } catch (Exception $exception) {
                Logger::Info('Album ' . $source->externalId . '  end at ' . $offset . ' offset with exception ' . $exception->getMessage());
                continue;
            }

            $this->saveFeedPosts($source, $posts);
        }
    }

    public function  getAlbums()
    {
        $target_feeds = TargetFeedFactory::Get( array( 'type' => TargetFeedUtility::VK ));
        $a = new ParserVkontakte();
        foreach( $target_feeds as $target_feed ) {
            //пропускаем тестовый паблик
            if ( in_array($target_feed->targetFeedId, $this->skipTargetFeedIds ))
                continue;
            //получаем список уже парсящихся альбомов

            $sql =  'SELECT
                            "externalId", "sourceFeedId"
                         FROM
                            "sourceFeeds"
                         WHERE
                            "externalId" LIKE @public_search
                            AND type = @type';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
            $cmd->SetString( '@public_search', "%". $target_feed->externalId ."_%" );
            $cmd->SetString( '@type', SourceFeedUtility::Albums );
            $ds = $cmd->Execute();

            $albums_array_from_base = array();
            while( $ds->Next()) {
                $albums_array_from_base[] = $ds->GetValue('externalId');;
            }

            $albums_array_from_vk = $a->get_public_albums( $target_feed->externalId );
            foreach( $albums_array_from_vk as $album_id => $data ) {
                $full_album_id = $target_feed->externalId . '_' . $album_id;
                if ( in_array( $full_album_id, $albums_array_from_base )) {
                    continue;
                }
                $source = new SourceFeed();
                $source->externalId = $full_album_id;
                $source->processed = null;
                $source->targetFeedIds = $target_feed->targetFeedId;
                $source->type  = SourceFeedUtility::Albums;
                $source->title = $data['title'];
                $source->useFullExport = false;
                $source->statusId = StatusUtility::Enabled;
                SourceFeedFactory::Add( $source );
            }
        }
    }
}