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
                AND @now - interval '1 day' <= "articleQueues"."deleteAt"
                AND "articleQueues"."deleteAt" IS NOT NULL
                AND "articleQueues"."statusId" = @status
                AND "articleQueues"."sentAt" IS NOT NULL
                AND "articleQueues"."externalId" IS NOT NULL
                ORDER BY "articleQueues"."deleteAt" DESC;
sql;
        $sender = new SenderVkontakte();

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetDateTime('@now', DateTimeWrapper::Now());
        $cmd->SetInt('@status', StatusUtility::Finished);

        $ds = $cmd->Execute();
        $structure = BaseFactory::getObjectTree($ds->Columns);
        while ($ds->next()) {
            /** @var $articleQueue ArticleQueue */
            $articleQueue = BaseFactory::GetObject($ds, ArticleQueueFactory::$mapping, $structure);
            $targetFeed = TargetFeedFactory::GetById($articleQueue->targetFeedId, array(), array(BaseFactory::WithLists => true));
            if ($targetFeed->type == TargetFeedUtility::VK) {

                if (empty($targetFeed) || empty($targetFeed->publishers) || empty($articleQueue)) {
                    continue;
                }

                foreach ($targetFeed->publishers as $publisher) {
                    try {
                        $sender->vk_app_seckey = $publisher->publisher->vk_seckey;
                        $sender->vk_access_token = $publisher->publisher->vk_token;
                        if ( $sender->delete_post($articleQueue->externalId)) {
                            $articleQueue->isDeleted = true;
                            ArticleQueueFactory::UpdateByMask($articleQueue, array('isDeleted'), array('articleQueueId' => $articleQueue->articleQueueId));
                        }
                        break;
                    } catch(Exception $exception) {
                        Logger::Warning('Exception on delete post over VK:API :' . $exception->getMessage());
                        AuditUtility::CreateEvent('deleteErrors', 'articleQueue', $articleQueue->articleQueueId, $exception->getMessage());
                        continue;
                    }
                }
            } else if ($targetFeed->type == TargetFeedUtility::FB) {
                // TODO
                continue;
            } else {
                continue;
            }

        }

        ConnectionFactory::CommitTransaction(true);
    }
}
