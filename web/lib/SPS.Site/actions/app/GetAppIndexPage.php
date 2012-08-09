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
            $author = Session::getObject('Author');

            //паблики, к которым у пользователя есть доступ
            $targetFeeds = TargetFeedFactory::Get(
                array('_targetFeedId' => Session::getArray('targetFeedIds'))
            );

            $targetFeedIdsWithPosts = array();
            $sql = <<<eof
                SELECT DISTINCT "targetFeedId"
                FROM "getArticles"
                WHERE "sourceFeedId" = -1
                AND "authorId" = @authorId
                GROUP BY "targetFeedId"
eof;
            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInteger('@authorId', $author->authorId);
            $ds = $cmd->Execute();
            while ($ds->Next()) {
                $targetFeedIdsWithPosts[] = $ds->GetInteger('targetFeedId');
            }

            // счетчик событий автора
            $authorCounter = AuthorEventUtility::GetAuthorCounter($author->authorId);

            Response::setArray( 'targetFeeds', $targetFeeds );
            Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($targetFeeds, 'targetFeedId') );
            Response::setArray( 'targetFeedIdsWithPosts', $targetFeedIdsWithPosts );
            Response::setInteger( 'authorCounter', $authorCounter );
        }
    }
?>