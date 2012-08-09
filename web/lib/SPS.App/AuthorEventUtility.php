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
            $event->isSent = true;

            //плохо, но у нас не отлавливается duplicate PK, а upsert не хочется писать
            $result = @AuthorEventFactory::Add($event);
            if (!$result) {
                AuthorEventFactory::UpdateByMask($event, array('isSent'), array('articleId' => $event->articleId));
            }
        }

        public static function EventQueueRemove($articleId) {
            $event = new AuthorEvent();
            $event->isSent = false;
            AuthorEventFactory::UpdateByMask($event, array('isSent'), array('articleId' => $articleId));
        }

        public static function EventComment(Article $article, $commentId) {
            $event = new AuthorEvent();
            $event->articleId = $article->articleId;
            $event->authorId = $article->authorId;
            $event->isSent = false;
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
            $result = 0;

            $sql = <<<sql
                SELECT
                    SUM((CASE WHEN "isSent" = true THEN 1 ELSE 0 END + coalesce(array_length("commentIds", 1), 0))) as "counter"
                FROM "authorEvents"
                WHERE "authorId" = @authorId
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@authorId', $authorId);
            $ds = $cmd->Execute();

            while ($ds->Next()) {
                $result = $ds->GetInteger('counter');
            }

            return $result;
        }
    }
?>