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
            $sql = 'SELECT * FROM "articleQueues" WHERE "statusId" = 1 LIMIT 1 FOR UPDATE;';
            $cmd = new SqlCommand( $sql, ConnectionFactory::Get() );

            $ds         = $cmd->Execute();
            $structure  = BaseFactory::getObjectTree( $ds->Columns );
            while ($ds->next() ) {
                $object = BaseFactory::GetObject( $ds, ArticleQueueFactory::$mapping, $structure );
            }

//            if (!empty($object)) {
//                $o = new ArticleQueue();
//                $o->statusId = 4; //queued
//                ArticleQueueFactory::UpdateByMask($o, array('statusId'), array('articleQueueId' => $object->articleQueueId) );
//            }
        }
    }
?>