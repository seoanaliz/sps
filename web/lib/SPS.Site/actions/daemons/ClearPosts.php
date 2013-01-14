<?php
    Package::Load( 'SPS.Site' );

    /**
     * ClearPosts Action
     * @package    SPS
     * @subpackage Site
     * @author     eugeneshulepin
     */
    class ClearPosts {

        private function getRemoved() {
            $sql = <<<sql
                select a."articleId" from articles a
                LEFT join "articleQueues" aq USING ("articleId")
                where a."statusId" = 3 and aq."articleId" IS NULL
                and "isCleaned" = false
                order by a."articleId"
                limit 5000;
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $articleIds = array();

            while ($ds->Next()) {
                $articleIds[] = $ds->GetInteger('articleId');
            }

            return $articleIds;
        }

        private function getPackUnused1() {
            $sql = <<<sql
                select a."articleId" from articles a
                LEFT join "articleQueues" aq USING ("articleId")
                where a."statusId" = 1 and aq."articleId" IS NULL
                and a."sourceFeedId" IN(2, 97, 108, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 128, 130, 131, 133, 138, 140, 146, 151, 156, 157, 163, 344)
                and "isCleaned" = false
                order by a."articleId"
                limit 5000;
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $articleIds = array();

            while ($ds->Next()) {
                $articleIds[] = $ds->GetInteger('articleId');
            }

            return $articleIds;
        }

        private function getPackUnused2() {
            $sql = <<<sql
                select a."articleId" from articles a
                LEFT join "articleQueues" aq USING ("articleId")
                where a."statusId" = 1 and aq."articleId" IS NULL
                and a."sourceFeedId" IN (75,200,92,210,15,150,151,155,88,89,92,93,95,1,183,26,36,3)
                and a."createdAt" < (now() - interval '1 month')
                and "isCleaned" = false
                order by a."articleId"
                limit 5000;
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $articleIds = array();

            while ($ds->Next()) {
                $articleIds[] = $ds->GetInteger('articleId');
            }

            return $articleIds;
        }

        private function getPackUnused3() {
            $sql = <<<sql
                select a."articleId" from articles a
                LEFT join "articleQueues" aq USING ("articleId")
                where a."statusId" = 1 and aq."articleId" IS NULL
                and "authorId" is null
                and a.rate < 80
                and a."createdAt" < (now() - interval '6 months')
                and "isCleaned" = false
                order by a."articleId"
                limit 5000;
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $articleIds = array();

            while ($ds->Next()) {
                $articleIds[] = $ds->GetInteger('articleId');
            }

            return $articleIds;
        }

        private function getTopUnused() {
            $sql = <<<sql
                select a."articleId" from articles a
                LEFT join "articleQueues" aq USING ("articleId")
                where a."statusId" = 1 and aq."articleId" IS NULL
                and a."sourceFeedId" IN (174, 196)
                and a."createdAt" < (now() - interval '1 week')
                and "isCleaned" = false
                order by a."articleId"
                limit 5000;
sql;

            $cmd = new SqlCommand($sql, ConnectionFactory::Get());
            $ds = $cmd->Execute();

            $articleIds = array();

            while ($ds->Next()) {
                $articleIds[] = $ds->GetInteger('articleId');
            }

            return $articleIds;
        }

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            $articleIds = $this->getRemoved();

            if (empty($articleIds)) {
                $articleIds = $this->getPackUnused1();
            }

            if (empty($articleIds)) {
                $articleIds = $this->getPackUnused2();
            }

            if (empty($articleIds)) {
                $articleIds = $this->getTopUnused();
            }

            if (empty($articleIds)) {
                $articleIds = $this->getPackUnused3();
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
                            if (empty($photo['filename'])) continue;
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
            $o->statusId = 3;
            ArticleFactory::UpdateByMask($o, array('isCleaned', 'statusId'), array('_articleId' => $articleIds));
        }
    }

?>