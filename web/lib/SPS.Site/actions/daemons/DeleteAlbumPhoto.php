<?php
/**
 * User: x100up
 * Date: 28.10.12 14:17
 * In Code We Trust
 */
class DeleteAlbumPhoto
{

    const PAUSE = 0.5;

    public function Execute()
    {
        set_time_limit(0);
        Logger::LogLevel(ELOG_DEBUG);
        ConnectionFactory::BeginTransaction();


        $targetFeeds = TargetFeedFactory::Get(array(), array(BaseFactory::WithLists => true) );
        $sender = new SenderVkontakte();
//попабликово выбираем по 10 фоток на удаление
        foreach( $targetFeeds as $targetFeed ) {
            $sql = <<<sql
                SELECT
                  a.*, sf."sourceFeedId"
                FROM
                  "articles" as a
                JOIN
                  "sourceFeeds" as sf
                USING ("sourceFeedId")
                WHERE
                      a."externalId" LIKE '%@targetFeedExternalId_%'
                  AND sf."type" = @sourceType
                  AND a."articleStatus" = @articleStatus
                  AND a."statusId" = @status
                LIMIT 10;
sql;
            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt( '@targetFeedExternalId', $targetFeed->externalId );
            $cmd->SetString( '@articleStatus', Article::STATUS_REJECT );
            $cmd->SetString( '@sourceType', SourceFeedUtility::Albums );
            $cmd->SetInt( '@status', StatusUtility::Enabled );
            $ds = $cmd->Execute();
            $structure = BaseFactory::getObjectTree($ds->Columns);
            /**
            *  массив источников - альбомов, из которых удаляем фотки
            */
            $processed_sources_array = array();
            while ($ds->next()) {
                /** @var $article Article */
                $article = BaseFactory::GetObject($ds, ArticleFactory::$mapping, $structure);
                if ($targetFeed->type == TargetFeedUtility::VK) {

                    if (empty($targetFeed) || empty($targetFeed->publishers) || empty($article)) {
                        continue;
                    }
                    shuffle($targetFeed->publishers);
                    foreach ($targetFeed->publishers as $publisher) {
                        try {
                            sleep( self::PAUSE );
                            $sender->vk_access_token = $publisher->publisher->vk_token;
                            $sender->delete_photo($article->externalId);
                            break;
                        } catch(Exception $exception) {
                            Logger::Warning('Exception on delete photo over VK:API :' . $exception->getMessage());
                            continue;
                        }
                    }
                } else if ($targetFeed->type == TargetFeedUtility::FB) {
                    // TODO
                    continue;
                } else {
                    continue;
                }

                $article->statusId = StatusUtility::Deleted;
                ArticleFactory::UpdateByMask( $article, array('statusId'), array('articleId' => $article->articleId ));
                $processed_sources_array[] = $ds->GetInteger('sourceFeedId');
            }
        }
//        TODO количество фоток в альбоме поменялось, какие удалили - хз. посему надо дальше парсить этот альбом
//        $processed_sources_array = array_unique($processed_sources_array);
//        $o = new SourceFeed();
//        $o->processed = 0;
//        if( !empty( $processed_sources_array )) {
//            SourceFeedFactory::UpdateByMask($o, array('processed'), array('_sourceFeedId' => $processed_sources_array));
//        }
        ConnectionFactory::CommitTransaction(true);
    }
}
