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
        set_time_limit(100);
        Logger::LogLevel(ELOG_DEBUG);


        $sql = <<<sql
                SELECT "articleQueues".*
                FROM "articleQueues"
                WHERE
                "articleQueues"."isDeleted" = FALSE
                AND @now >= "articleQueues"."deleteAt"
                AND now() - interval '1 day' <= "articleQueues"."deleteAt"
                AND "articleQueues"."deleteAt" IS NOT NULL
                AND "articleQueues"."statusId" = @status
                AND "articleQueues"."sentAt" IS NOT NULL
                AND "articleQueues"."externalId" IS NOT NULL
                ORDER BY "articleQueues"."deleteAt" DESC;
                LIMIT 15
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
                sleep(0.3);
                if (empty($targetFeed) || empty($articleQueue)) {
                    continue;
                }


                $roles = array( UserFeed::ROLE_OWNER, UserFeed::ROLE_EDITOR, UserFeed::ROLE_ADMINISTRATOR);

                $tokens = AccessTokenUtility::getAllTokens( $targetFeed->targetFeedId, AuthVkontakte::$Version, $roles);

                foreach ($tokens as $token ) {
                    try {
                        $sender->vk_access_token = $token;
                        if ( $sender->delete_post($articleQueue->externalId)) {
                            $articleQueue->isDeleted = true;
                            ArticleQueueFactory::UpdateByMask($articleQueue, array('isDeleted'), array('articleQueueId' => $articleQueue->articleQueueId));
                        }
                        break;
                    } catch(Exception $exception) {
                        sleep(0.5);
                        Logger::Warning('Exception on delete post over VK:API :' . $exception->getMessage());
                        sleep(0.4);
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
    }
}
