<?php
/**
 * User: x100up
 * Date: 28.10.12 14:17
 * In Code We Trust
 */
class DeletePost
{
    public function Execute()
    {
        set_time_limit(0);
        Logger::LogLevel(ELOG_DEBUG);
        ConnectionFactory::BeginTransaction();

        $sql = <<<sql
                SELECT "articles".*, "articleQueues".*
                FROM "articles", "articleQueues"
                WHERE
                "articles"."isDeleted" = FALSE
                AND @now >= "articles"."deleteAt"
                AND "articles"."articleId" = "articleQueues"."articleId"
                LIMIT 10 FOR UPDATE;
sql;
        $sender = new SenderVkontakte();

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetDateTime('@now', DateTimeWrapper::Now());

        $ds = $cmd->Execute();
        $structure = BaseFactory::getObjectTree($ds->Columns);
        while ($ds->next()) {
            /** @var $article Article */
            $article = BaseFactory::GetObject($ds, ArticleFactory::$mapping, $structure);
            /** @var $articleQueue ArticleQueue */
            $articleQueue = BaseFactory::GetObject($ds, ArticleQueueFactory::$mapping, $structure);

            $targetFeed = TargetFeedFactory::GetById($articleQueue->targetFeedId, array(), array(BaseFactory::WithLists => true));

            if ($targetFeed->type == TargetFeedUtility::VK) {
                try {
                    $sender->delete_post($articleQueue->externalId);
                } catch(Exception $exception) {
                    Logger::Warning('Exception on delete post over VK:API :' . $exception->getMessage());
                    continue;
                }
            } else if ($targetFeed->type == TargetFeedUtility::FB) {
                // TODO
                continue;
            } else {
                continue;
            }

            $article->isDeleted = true;
            ArticleFactory::UpdateByMask($article, array('isDeleted'), array('articleId' => $article->articleId));
        }

        ConnectionFactory::CommitTransaction(true);
    }
}
