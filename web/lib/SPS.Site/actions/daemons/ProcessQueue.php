<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );

    /**
     * ProcessQueue Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ProcessQueue {

        /**
         * Entry Point
         */
        public function Execute() {
            set_time_limit(0);
            Logger::LogLevel(ELOG_DEBUG);

            ConnectionFactory::BeginTransaction();

            //атомарно занимаем запись в очереди
            //берем только ту запись, которую нужно отправить по времени
            //валера, твое время настало!
            $sql = <<<sql
                SELECT * FROM "articleQueues"
                WHERE "statusId" = 1
                AND @now >= "startDate"
                AND @now <= "endDate"
                LIMIT 1 FOR UPDATE;
sql;

            $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );
            $cmd->SetDateTime('@now', DateTimeWrapper::Now());

            $ds         = $cmd->Execute();
            $structure  = BaseFactory::getObjectTree( $ds->Columns );
            while ($ds->next() ) {
                $object = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
            }

            if (!empty($object)) {
                $o = new ArticleQueue();
                $o->statusId = StatusUtility::Queued;
                //ArticleQueueFactory::UpdateByMask($o, array('statusId'), array('articleQueueId' => $object->articleQueueId) );
                ConnectionFactory::CommitTransaction(true);
            } else {
                ConnectionFactory::CommitTransaction(false);
                return;
            }

            ConnectionFactory::CommitTransaction(true);

            //$this->finishArticleQueue($object->articleQueueId);
        }

        private function finishArticleQueue($articleQueueId) {
            $o = new ArticleQueue();
            $o->statusId = StatusUtility::Finished;
            $o->sentAt = DateTimeWrapper::Now();
            ArticleQueueFactory::UpdateByMask($o, array('statusId', 'sentAt'), array('articleQueueId' => $articleQueueId) );
        }
    }
?>