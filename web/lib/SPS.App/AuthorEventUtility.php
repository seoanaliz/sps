<?php
    /**
     * AuthorEventUtility
     * @package    SPS
     * @subpackage App
     * @author     Shuler
     */
    class AuthorEventUtility {

        public static function EventQueue(Article $article) {
            $event = new AuthorEvent();
            $event->articleId = $article->articleId;
            $event->authorId = $article->authorId;
            $event->isQueued = true;
            $event->isSent = false;

            //плохо, но у нас не отлавливается duplicate PK, а upsert не хочется писать
            $result = @AuthorEventFactory::Add($event);
            if (!$result) {
                AuthorEventFactory::UpdateByMask($event, array('isQueued'), array('articleId' => $event->articleId));
            }
        }

        public static function EventQueueRemove($articleId) {
            $event = new AuthorEvent();
            $event->isQueued = false;
            AuthorEventFactory::UpdateByMask($event, array('isQueued'), array('articleId' => $articleId));
        }

        public static function EventSent(Article $article) {
            $event = new AuthorEvent();
            $event->articleId = $article->articleId;
            $event->authorId = $article->authorId;
            $event->isQueued = false;
            $event->isSent = true;

            //плохо, но у нас не отлавливается duplicate PK, а upsert не хочется писать
            $result = @AuthorEventFactory::Add($event);
            if (!$result) {
                AuthorEventFactory::UpdateByMask($event, array('isQueued', 'isSent'), array('articleId' => $event->articleId));
            }
        }

        public static function EventSentRemove($articleId) {
            $event = new AuthorEvent();
            $event->isSent = false;
            AuthorEventFactory::UpdateByMask($event, array('isSent'), array('articleId' => $articleId));
        }

        public static function EventComment(Article $article, $commentId) {
            $event = new AuthorEvent();
            $event->articleId   = $article->articleId;
            $event->authorId    = $article->authorId;
            $event->isQueued    = false;
            $event->isSent      = false;
            $event->commentIds[] = $commentId;

            $result = @AuthorEventFactory::Add($event);
            if (!$result) {
                $sql = <<<sql
                  UPDATE "authorEvents" SET "commentIds" = array_append("commentIds", @commentId)
                  WHERE "articleId" = @articleId
sql;
                $cmd = new SqlCommand($sql, ConnectionFactory::Get());
                $cmd->SetInt('@commentId', $commentId);
                $cmd->SetInt('@articleId', $article->articleId);
                $cmd->ExecuteNonQuery();
            }
        }

        public static function EventCommentRemove(Article $article, $commentId) {
            $event = new AuthorEvent();
            $event->articleId = $article->articleId;

            $sql = <<<sql
              UPDATE "authorEvents" SET "commentIds" = array_remove_sql(CAST("commentIds" as int8[]), CAST('{@commentId}' as int8[]))
              WHERE "articleId" = @articleId
sql;
            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@commentId', $commentId);
            $cmd->SetInt('@articleId', $article->articleId);
            $cmd->ExecuteNonQuery();
        }

        public static function GetAuthorCounter($authorId) {
            $result = array();

            $sql = <<<sql
                SELECT
                    SUM(coalesce(array_length("commentIds", 1), 0)) as "newComments",
                    SUM(CASE WHEN "isQueued" = true THEN 1 ELSE 0 END) as "newQueued",
                    SUM(CASE WHEN "isSent" = true THEN 1 ELSE 0 END) as "newSent"
                FROM "authorEvents"
                WHERE "authorId" = @authorId
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@authorId', $authorId);
            $ds = $cmd->Execute();

            while ($ds->Next()) {
                $result['newComments'] = $ds->GetInteger('newComments');
                $result['newQueued'] = $ds->GetInteger('newQueued');
                $result['newSent'] = $ds->GetInteger('newSent');
                $result['total'] = $result['newComments'] + $result['newQueued'] + $result['newSent'];
            }

            return $result;
        }
    }
?>