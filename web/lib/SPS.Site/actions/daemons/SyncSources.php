<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );
    Package::Load( 'SPS.VK' );
    include_once('AbstractPostLoadDaemon.php');

    /**
     * SyncSources Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SyncSources extends AbstractPostLoadDaemon {



        /**
         * Один вызов этого метода просинхронизирует только одну страницу каждого sourceFeed
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $this->daemon                   = new Daemon();
            $this->daemon->package          = 'SPS.Site';
            $this->daemon->method           = 'SyncSources';
            $this->daemon->maxExecutionTime = '01:00:00';

            //get sources
            $sources = SourceFeedFactory::Get(array('type' => SourceFeedUtility::Source));

            foreach ($sources as $source) {
                //пропускаем специальные источники
                if (SourceFeedUtility::IsTopFeed($source) || $source->externalId == '-') {
                    continue;
                }

                //инитим парсер
                $parser = new ParserVkontakte(trim($source->externalId));

                try {
                    $count = $parser->get_posts_count();
                } catch (Exception $Ex) {
                    $message = $Ex->getMessage();

                    //wall's end exclude
                    if (strpos($message, "wall's end") === false) {
                        AuditUtility::CreateEvent('importErrors', 'feed', $source->externalId, $message);
                    }

                    if (strpos($message, 'access denied') !== false) {
                        $source->statusId = 2;
                        SourceFeedFactory::Update($source);
                        AuditUtility::CreateEvent('importErrors', 'feed', $source->externalId, 'auto disabled');
                    }

                    //переходим к след источнику
                    continue;
                }

                $pagesCountTotal = ceil($count / ParserVkontakte::PAGE_SIZE);

                $pagesCountProcessed = Convert::ToInt($source->processed);
                //если кол-во обработанных страниц в source меньше $pagesCount - работаем
                if ($pagesCountTotal > $pagesCountProcessed) {
                    //парсим одну нужную страницу
                    $targetPage = $pagesCountTotal - 1 - $pagesCountProcessed;

                    //пытаемся залочиться
                    $this->daemon->name = "source$source->externalId";
                    if ( !$this->daemon->Lock() ) {
                        Logger::Warning( "Failed to lock {$this->daemon->name}");
                        continue; //переходим к следующему sorce
                    }

                    try {
                        $posts = $parser->get_posts($targetPage, !empty($source->useFullExport));
                    } catch (Exception $Ex) {
                        AuditUtility::CreateEvent('importErrors', 'feed', $source->externalId, $Ex->getMessage());
                        continue; //переходим к следующему sorce
                    }

                    $posts = !empty($posts) ? $posts : array();

                    $this->saveFeedPosts($source, $posts);
                    die();
                }
            }
        }
    }