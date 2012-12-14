<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetAppIndexPage Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetAppIndexPage {

        /**
         * Entry Point
         */
        public function Execute() {
            /** @var $author Author */
            $author = Session::getObject('Author');

            $RoleAccessUtility = new RoleAccessUtility($author->vkId);

            // TODO сделать граммотно
            $targetFeedIds = array();
            $rawTargetFeedIds = $RoleAccessUtility->getTargetFeedIds();
            foreach ($rawTargetFeedIds as $role=>$ids) {
                foreach ($ids as $id) {
                    $targetFeedIds[$id] = $id;
                }
            }

            //паблики, к которым у пользователя есть доступ
            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => $targetFeedIds)
            );


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
            $targetCounters = AuthorFeedViewUtility::GetCounters($author->authorId);

            Response::setArray( 'targetFeeds', $targetFeeds );
            Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId') );
            Response::setArray( 'targetFeedIdsWithPosts', $targetFeedIdsWithPosts );
            Response::setArray( 'authorCounter', $authorCounter );
            Response::setArray( 'targetCounters', $targetCounters );
            Response::setString('tabType', Session::getString( 'gaal_tabType' ));
        }
    }
?>