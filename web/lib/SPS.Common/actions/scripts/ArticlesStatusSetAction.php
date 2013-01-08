<?php
/**
 * User: x100up
 * Date: 07.01.13 12:20
 * In Code We Trust
 */

/**
 * #11120
 * Перевести все авторские записи в "Новые", кроме уже опубликованных и запланированных, которым назначаем статус "Одобренно"
 */
class ArticlesStatusSetAction
{
    public function Execute() {
        // авторские незапланированые в Новые
        $sql = 'UPDATE "articles" SET "articleStatus" = @status
                WHERE "authorId" IS NOT NULL
                AND "queuedAt" IS NULL
                AND "sentAt" IS NULL';

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetInt('@status', Article::STATUS_REVIEW);

        $ds = $cmd->Execute();

        // авторские  запланированеы в Одобренные
        $sql = 'UPDATE "articles" SET "articleStatus" = @status
                WHERE "authorId" IS NOT NULL
                AND ("queuedAt" IS NOT NULL OR "sentAt" IS NOT NULL)';

        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetInt('@status', Article::STATUS_APPROVED);

        $ds = $cmd->Execute();

        echo 'success';
    }
}
