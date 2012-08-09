<?php
    /**
     * AuthorFeedViewUtility
     * @package    SPS
     * @subpackage App
     * @author     Shuler
     */
    class AuthorFeedViewUtility {

        public static function GetCounters($authorId) {
            $result = array();

            $sql = <<<sql
                SELECT afv."targetFeedId", count(a."articleId") as "count"
                FROM "authorFeedViews" afv
                LEFT JOIN "articles" a ON (
                    "sourceFeedId" = -1
                    AND "createdAt" > "lastViewDate"
                    AND a."authorId" != @authorId
                    AND "statusId" != 3
                    AND afv."targetFeedId" = a."targetFeedId"
                )
                WHERE afv."authorId" = @authorId
                GROUP BY afv."targetFeedId"
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@authorId', $authorId);

            $ds = $cmd->Execute();

            while ($ds->Next()) {
                $result[$ds->GetInteger('targetFeedId')] = $ds->GetInteger('count');
            }

            return $result;
        }

        public static function UpdateLastView($authorId, $targetFeedId) {
            $object = new AuthorFeedView();
            $object->authorId = $authorId;
            $object->targetFeedId = $targetFeedId;
            $object->lastViewDate = DateTimeWrapper::Now();

            $result = @AuthorFeedViewFactory::Add($object);
            if (!$result) {
                AuthorFeedViewFactory::UpdateByMask(
                    $object
                    , array('lastViewDate')
                    , array('authorId' => $authorId, 'targetFeedId' => $targetFeedId)
                );
            }
        }
    }
?>