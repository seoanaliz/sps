<?php
    Package::Load( 'SPS.Site' );

    /**
     * ClearPosts Action
     * @package    SPS
     * @subpackage Site
     * @author     eugeneshulepin
     */
    class ClearPosts {

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $sql = <<<sql
                select a."articleId" from articles a
                LEFT join "articleQueues" aq USING ("articleId")
                where a."statusId" = 3 and aq."articleId" IS NULL
                and "isCleaned" = false
                order by a."articleId"
                limit 200;
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $articleIds = array();

            while ($ds->Next()) {
                $articleIds[] = $ds->GetInteger('articleId');
            }

            if (empty($articleIds)) {
                return;
            }

            $articleRecords = ArticleRecordFactory::Get(array('_articleId' => $articleIds));

            $fileNames = array();
            if (!empty($articleRecords)) {
                foreach ($articleRecords as $articleRecord) {
                    if (!empty($articleRecord->photos)) {
                        foreach ($articleRecord->photos as $photo) {
                            $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photo['filename'] );
                            $path = str_replace('http://' . MediaServerManager::$Host, '/home/sps/photos', $path);

                            $fileNames[] = $path;
                            $fileNames[] = str_replace('/original/', '/small/', $path);
                        }
                    }
                }
            }

            foreach ($fileNames as $fileName) {
                Logger::Debug('unlink ' . $fileName);
                @unlink($fileName);
            }

            $o = new Article();
            $o->isCleaned = true;
            ArticleFactory::UpdateByMask($o, array('isCleaned'), array('_articleId' => $articleIds));
        }
    }

?>