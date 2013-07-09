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
                //времянка, пока не перейдем на новую систему токенов
                $token = AccessTokenUtility::getPublisherTokenForTargetFeed($targetFeed, true );
                if( !$token ) {
                    $token = AccessTokenUtility::getTokenForTargetFeed($targetFeed, true);
                }
                //мы упрямые!
                if( !$token) {
                    Logger::Warning( "No token for targetFeed {$targetFeed->targetFeedId}");
                    continue;
                }

                $this->daemon->name = "feed$targetFeed->externalId";
                if ( !$this->daemon->Lock() ) {
                    Logger::Warning( "Failed to lock {$this->daemon->name}");
                    continue;
                }
                if( $targetFeed->targetFeedId == 9826) {
                    $token  =   'c4d2030b23785030efb9496bacd80f78f33bc91fd00c78b69810bfb99b1c8ec1864b657924dc72be0131c';}
                elseif( in_array($targetFeed->targetFeedId, array(33,27))) {
                    $token  =   '1ae17c839729af411c04ca697f7d63cb767125beed88ac62b5b0de6b44d6664e783ac20739ba4157e6527';
                } else {
                    $token  =   'ab819c5c52b436c614fc9e1769f63168b44860a01387d2689fddb1fd56d23041178784b50d535610637b5';
                }

                $parser->set_page( $targetFeed->externalId );
                try {
                    $posts = $parser->get_suggested_posts( $targetFeed->params['lastSuggestedPost'], $token );
                } catch (Exception $Ex) {

                    Logger::Warning( "Import error:  {$Ex->getMessage()}");
                    AuditUtility::CreateEvent('importErrors', 'feed', $targetFeed->externalId, $Ex->getMessage());
                    continue;
                }

                if( !isset($posts[0])) {
                    Logger::Warning( "Up to date:  {$targetFeed->externalId}. ");
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
        $sql = 'SELECT * FROM "targetFeeds" WHERE "collectSuggests" = true and "externalId" in (\'10639516\',\'52833601\', \'36775802\', \'26776509\' , \'34010064\' ) OFFSET @offset LIMIT @limit';         
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
        //добавляем новых авторов
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
                $newAuthors[$vkId] = $newAuthor;
            }
        }
        $authors = $authors + $newAuthors;

        //прописываем роли авторам
        $userFeeds = UserFeedFactory::Get( array(
            'targetFeedId' => $targetFeedId
        ));//берем из базы уже имеющиеся
        $userFeeds = ArrayHelper::Collapse($userFeeds, 'vkId', false);
        foreach($authors as $vkId => $author) {
            if( !isset( $userFeeds[$vkId] )){
                $newUserFeeds[] = new UserFeed($vkId, $targetFeedId, UserFeed::ROLE_AUTHOR);
            }
        }
        if(!empty($newUserFeeds)) {
            UserFeedFactory::AddRange($newUserFeeds);
        }
        return $authors;
    }
}
