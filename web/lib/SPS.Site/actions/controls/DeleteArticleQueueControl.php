<?php
    /**
     * DeleteArticleQueueControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteArticleQueueControl extends BaseControl {

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

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            //check access
            if (!$TargetFeedAccessUtility->canDeleteArticlesFromQueue($object->targetFeedId)) {
                return;
            }

            $o = new ArticleQueue();
            $o->statusId = 3;
            $o->deleteAt = null;
            $o->isDeleted = false;
            ArticleQueueFactory::UpdateByMask($o, array('statusId', 'deleteAt', 'isDeleted'), array('articleQueueId' => $id));

            //пытаемся восстановить статью, которую заблокировали
            if (!empty($object)) {

                $o = new Article();
                $o->statusId = 1;
                $o->queuedAt = null;
                ArticleFactory::UpdateByMask($o, array('statusId', 'queuedAt'), array('articleId' => $object->articleId, 'statusId' => 2));

                AuthorEventUtility::EventQueueRemove($object->articleId);

                AuditUtility::CreateEvent(
                    'articleQueueDelete',
                    'article',
                    $object->articleId,
                    "QueueId $id deleted by editor VkId " . AuthUtility::GetCurrentUser('Editor')->vkId . " UserId " . AuthUtility::GetCurrentUser('Editor')->editorId
                );
            }
        }
    }
?>