<?php
    Package::Load( 'SPS.Reports' );

    /**
     * TopfaceUserPosts Action
     * @package    SPS
     * @subpackage Reports
     * @author     eugeneshulepin
     */
    class TopfaceUserPosts {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array();

            $sql = <<<sql
                select
                    a."articleId"
                    , ar."topfaceData"
                    , a."importedAt"
                    , a."statusId"
                    , aq."sentAt"
                    , aq."externalId"
                from "articles" a
                LEFT JOIN "articleRecords" ar USING ("articleId")
                LEFT JOIN "articleQueues" aq USING ("articleId")
                where "sourceFeedId" = @sourceFeedId
                order by a."importedAt" DESC
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $cmd->SetInt('@sourceFeedId', SourceFeedUtility::FakeSourceTopface);
            $ds = $cmd->Execute();

            while ($ds->Next()) {
                $id = 'article_' . $ds->GetInteger('articleId');
                $importedAt = $ds->GetDateTime('importedAt');
                $sentAt = $ds->GetDateTime('sentAt');

                $result[$id] = array(
                    'topfaceData' => $ds->GetComplexType('topfaceData', 'php'),
                    'importedAt' => !empty($importedAt) ? $importedAt->format('U') : null,
                    'sentAt' => !empty($sentAt) ? $sentAt->format('U') : null,
                    'statusId' => $ds->GetInteger('statusId'),
                    'externalId' => $ds->GetString('externalId'),
                );
            }

            header("Content-Type: text/html; charset=utf-8");
            echo ObjectHelper::ToJSON($result);
        }
    }
?>