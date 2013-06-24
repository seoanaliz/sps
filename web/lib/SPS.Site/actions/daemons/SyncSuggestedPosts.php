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
    class SyncSuggestedPosts extends AbstractPostLoadDaemon {


        const FeedsChunkSize = 100;
        /**
         * Один вызов этого метода просинхронизирует только одну страницу каждого sourceFeed
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $this->daemon                   = new Daemon();
            $this->daemon->package          = 'SPS.Site';
            $this->daemon->method           = 'SyncSuggestedPosts';
            $this->daemon->maxExecutionTime = '01:00:00';


            $targetFeeds = $this->getNextChunkOfFeeds();

            $parser = new ParserVkontakte();
            //делаем левый source
            $source = new SourceFeed();
            $source->sourceFeedId = -1;
            $source->type = SourceFeedUtility::Authors;

            while( !empty( $targetFeeds)) {
                $targetFeeds4update = array();
                foreach ($targetFeeds as $targetFeed ) {
                    if(!isset( $targetFeed->params['lastSuggestedPost']))
                        $targetFeed->params['lastSuggestedPost'] = 0;
                    $token = AccessTokenUtility::getTokenForTargetFeedId($targetFeed->targetFeedId, true);
                    if( !$token) {
                        Logger::Warning( "No token for targetFeed {$targetFeed->targetFeedId}");
                        continue;
                    }

                    $parser->set_page( $targetFeed->externalId );


                    $this->daemon->name = "feed$targetFeed->externalId";
                    if ( !$this->daemon->Lock() ) {
                        Logger::Warning( "Failed to lock {$this->daemon->name}");
                        continue;
                    }
                    try {
                        $posts = $parser->get_suggested_posts( $targetFeed->params['lastSuggestedPost'], $token->accessToken );
                    } catch (Exception $Ex) {
                        Logger::Warning( "Import error:  {$Ex->getMessage()}");
                        AuditUtility::CreateEvent('importErrors', 'feed', $targetFeed->externalId, $Ex->getMessage());
                        continue;
                    }

                    if( !isset($posts[0])) {
                        $this->daemon->Unlock();
                        continue;
                    }
                    $authors = $this->addAuthors($posts, $targetFeed->targetFeedId);
                    $this->saveFeedPosts($source, $posts, $targetFeed->targetFeedId, $authors);
                    $targetFeeds4update[] = $targetFeed;

                    $targetFeed->params['lastSuggestedPost'] = $posts[0]['pid'];
                    $this->daemon->Unlock();
                }
                TargetFeedFactory::UpdateRange( $targetFeeds4update );

                if (count($targetFeeds) < self::FeedsChunkSize)
                    break;
            }


        }

        /** @return TargetFeed[]*/
        private function getNextChunkOfFeeds()
        {
            if( !isset($offset))
                static $offset = 0;

            $targetFeeds = array();
            /** @var $targetFeeds TargetFeed[]*/
            $sql = 'SELECT * FROM "targetFeeds" WHERE "isOur" = true OFFSET @offset LIMIT @limit';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get());
            $cmd->SetInt( '@offset', $offset);
            $cmd->SetInt( '@limit', self::FeedsChunkSize);
            $ds = $cmd->Execute();

            $structure = BaseFactory::GetObjectTree( $ds->Columns );
            while( $ds->Next()) {
                $targetFeed = BaseFactory::GetObject( $ds, TargetFeedFactory::$mapping, $structure);
                $targetFeeds[$targetFeed->targetFeedId] = $targetFeed;
            }
            $offset += self::FeedsChunkSize;

            return $targetFeeds;
        }

        private function addAuthors( $posts, $targetFeedId ) {
            $authorsExternalIds = array();
            foreach( $posts as $post ) {
                $authorsExternalIds[] = $post['author'];
            }
            if (empty($authorsExternalIds))
                return false;

            $authorsExternalIds = array_unique( $authorsExternalIds);
            $vkAuthorsData = StatUsers::get_vk_user_info($authorsExternalIds);
            $authors = AuthorFactory::Get(array('vkIdIn' => $authorsExternalIds));

            if( !empty($authors)) {
                $authors = ArrayHelper::Collapse($authors, 'vkId', false);
            }

            $newAuthors = array();
            $newUserFeeds = array();
            foreach( $authorsExternalIds as $vkId ) {
                if(!isset( $authors[$vkId]) && isset($vkAuthorsData[$vkId])) {
                    $name = explode(' ',$vkAuthorsData[$vkId]['name']);
                    $newAuthor = new Author();
                    $newAuthor->avatar = $vkAuthorsData[$vkId]['ava'];
                    $newAuthor->vkId   = $vkId;
                    $newAuthor->firstName = $name[0];
                    $newAuthor->statusId  = StatusUtility::Enabled;
                    $newAuthor->lastName  = isset( $name[1]) ? $name[1] : '';
                    AuthorFactory::Add($newAuthor, array(BaseFactory::WithReturningKeys=>true));
                    $newUserFeeds[] = new UserFeed($vkId, $targetFeedId, UserFeed::ROLE_AUTHOR);
                    $newAuthors[$vkId] = $newAuthor;
                }
            }

            if(!empty($newUserFeeds)) {
                UserFeedFactory::AddRange($newUserFeeds);
            }

            $authors = $authors + $newAuthors;
            return $authors;
        }
    }