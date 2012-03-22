<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteAricleQueueControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteAricleQueueControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );
            if ($id) {
                $o = new ArticleQueue();
                $o->statusId = 3;
                ArticleQueueFactory::UpdateByMask($o, array('statusId'), array('articleQueueId' => $id));
            }
        }
    }
?>