<?php
    Package::Load( 'SPS.Site' );

    /**
     * DeleteArticleQueueControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteArticleQueueControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $object = ArticleQueueFactory::GetById($id);
            if (empty($object)) {
                return;
            }

            //check access
            if (!AccessUtility::HasAccessToTargetFeedId($object->targetFeedId)) {
                return;
            }

            $o = new ArticleQueue();
            $o->statusId = 3;
            ArticleQueueFactory::UpdateByMask($o, array('statusId'), array('articleQueueId' => $id));

            //пытаемся восстановить статью, которую заблокировали
            if (!empty($object)) {

                $o = new Article();
                $o->statusId = 1;
                $o->queuedAt = null;
                ArticleFactory::UpdateByMask($o, array('statusId', 'queuedAt'), array('articleId' => $object->articleId, 'statusId' => 2));

                AuthorEventUtility::EventQueueRemove($object->articleId);
            }
        }
    }
?>