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
                SELECT "articleQueues".*
                FROM "articleQueues"
                WHERE
                "articleQueues"."isDeleted" = FALSE
                AND @now >= "articleQueues"."deleteAt"
                LIMIT 10 FOR UPDATE;
sql;
        $sender = new SenderVkontakte();

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetDateTime('@now', DateTimeWrapper::Now());

        $ds = $cmd->Execute();
        $structure = BaseFactory::getObjectTree($ds->Columns);
        while ($ds->next()) {
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

            $articleQueue->isDeleted = true;
            ArticleQueueFactory::UpdateByMask($articleQueue, array('isDeleted'), array('articleQueueId' => $articleQueue->articleQueueId));
        }

        ConnectionFactory::CommitTransaction(true);
    }
}
