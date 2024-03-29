<?php
/**
 * AuthorFeedViewUtility
 * @package    SPS
 * @subpackage App
 * @author     Shuler
 */
class AuthorFeedViewUtility
{

    public static function GetCounters($authorId, $vkId)
    {
        $result = array();

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($vkId);
        $targetFeedIds = $TargetFeedAccessUtility->getAllTargetFeedIds();

        if (!$targetFeedIds) {
            return array();
        }

        //фиксим даты
        self::fixViews($authorId, $targetFeedIds);

        $sql = <<<sql
                SELECT afv."targetFeedId", count(a."articleId") as "count"
                FROM "authorFeedViews" afv
                LEFT JOIN "articles" a ON (
                    "sourceFeedId" = @sourceFeedId
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
        $cmd->SetInt('@sourceFeedId', SourceFeedUtility::FakeSourceAuthors);

        $ds = $cmd->Execute();

        while ($ds->Next()) {
            $result[$ds->GetInteger('targetFeedId')] = $ds->GetInteger('count');
        }

        return $result;
    }

    public static function UpdateLastView($authorId, $targetFeedId)
    {
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

    private static function fixViews($authorId, $targetFeedIds)
    {
        $objects = AuthorFeedViewFactory::Get(
            array('authorId' => $authorId, '_targetFeedId' => $targetFeedIds)
        );
        if (!empty($objects)) {
            $objects = BaseFactoryPrepare::Collapse($objects, 'targetFeedId');
        }

        $newObjects = array();
        foreach ($targetFeedIds as $targetFeedId) {
            if (empty($objects[$targetFeedId])) {
                $o = new AuthorFeedView();
                $o->authorId = $authorId;
                $o->targetFeedId = $targetFeedId;
                $o->lastViewDate = DateTimeWrapper::Now();

                $newObjects[] = $o;
            }
        }

        AuthorFeedViewFactory::AddRange($newObjects);
    }
}

?>