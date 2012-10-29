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
class SyncAlbums extends AbstractPostLoadDaemon
{

    /**
     * @var Daemon
     */
    private $daemon;

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
            $posts = $parser->get_album_as_posts($public_id, $album_id);
            //var_dump($posts);
            $this->saveFeedPosts($source, $posts);
        }
    }
}