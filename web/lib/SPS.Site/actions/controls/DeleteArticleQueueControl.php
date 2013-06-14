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
            $result = array(
                'html' => $this->renderEmptySlot()
            );
            echo ObjectHelper::ToJSON($result);
        }

        protected function renderEmptySlot() {
            $gridId = Request::getInteger('gridId');
            $canEditQueue = true;

            $timestamp = Request::getInteger('timestamp');
            $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);
            $grid = GridLineUtility::GetGrid(Request::getInteger('targetFeedId'), $date, Request::getString('type'));
            $gridItem = null;
            foreach ($grid as $key => $gi) {
                if ($gi['gridLineId'] == Request::getString('gridId')) {
                    $gridItem = $gi;
                    break; // --------------------- BREAK
                }
            }
            if (!$gridItem) {
                return ''; // --------------------- RETURN
            }

            ob_start();
            include Template::GetCachedRealPath('tmpl://fe/elements/articles-queue-list-item.tmpl.php');
            $html = ob_get_clean();
            return $html;
        }
    }
?>