<?php
    /**
     * DeleteArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class DeleteArticleControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger( 'id' );

            if (empty($id)) {
                return;
            }

            $object = ArticleFactory::GetById($id);
            if (empty($object)) {
                return;
            }

            $SourceAccessUtility = new SourceAccessUtility($this->vkId);

            //check access
            if (!$SourceAccessUtility->hasAccessToSourceFeed($object->sourceFeedId)) {
                return;
            }

            AuditUtility::CreateEvent(
                'articleDelete',
                'article',
                $id,
                "Deleted by editor VkId " . AuthUtility::GetCurrentUser('Editor')->vkId . " UserId " . AuthUtility::GetCurrentUser('Editor')->authorId
            );

            //topface moderation failed
            if ($object->sourceFeedId == SourceFeedUtility::FakeSourceTopface) {
                $articleRecord = ArticleRecordFactory::GetOne(array('articleId' => $id));
                TopfaceUtility::DeclinePost($object, $articleRecord);
            }

            $o = new Article();
            $o->statusId = 3;
            ArticleFactory::UpdateByMask($o, array('statusId'), array('articleId' => $id));
        }
    }
?>