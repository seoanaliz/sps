<?php
Package::Load('SPS.Site');

/**
 * GetAppIndexPage Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetAppIndexPage extends AppBaseControl {

    /**
     * Entry Point
     */
    public function Execute()
    {
        $author = $this->getAuthor();

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $targetFeeds = array();

        $targetFeedIds = $TargetFeedAccessUtility->getAllTargetFeedIds();
        if ($targetFeedIds) {
            //паблики, к которым у пользователя есть доступ
            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => $targetFeedIds)
            );
        }

        $targetFeedIdsWithPosts = array();
        $sql = <<<eof
                SELECT DISTINCT "targetFeedId"
                FROM "getArticles"
                WHERE "sourceFeedId" = @sourceFeedId
                AND "authorId" = @authorId
                GROUP BY "targetFeedId"
eof;
        $cmd = new SqlCommand($sql, ConnectionFactory::Get());
        $cmd->SetInteger('@authorId', $author->authorId);
        $cmd->SetInteger('@sourceFeedId', SourceFeedUtility::FakeSourceAuthors);
        $ds = $cmd->Execute();
        while ($ds->Next()) {
            $targetFeedIdsWithPosts[] = $ds->GetInteger('targetFeedId');
        }

        // счетчик событий автора
        $authorCounter = AuthorEventUtility::GetAuthorCounter($author->authorId);
        $targetCounters = AuthorFeedViewUtility::GetCounters($author->authorId, $this->vkId);

        Response::setArray('targetFeeds', $targetFeeds);
        Response::setArray('targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId'));
        Response::setArray('targetFeedIdsWithPosts', $targetFeedIdsWithPosts);
        Response::setArray('authorCounter', $authorCounter);
        Response::setArray('targetCounters', $targetCounters);
        Response::setString('tabType', Session::getString('gaal_tabType'));
    }
}

?>