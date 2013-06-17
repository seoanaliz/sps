<?php
    /**
     * RenderEmptyQueueItemControll Action
     * @package    SPS
     * @subpackage Site
     * @author     Eugene Kulikov
     */
    class RenderEmptyQueueItemControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $id = Request::getInteger('id');

            if (empty($id)) {
                return;
            }

            $object = ArticleQueueFactory::GetById($id);
            if (empty($object)) {
                return;
            }

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($object->targetFeedId);
            if (is_null($role)){
                return;
            }
            $canEditQueue = ($role != UserFeed::ROLE_AUTHOR);

            $result = array(
                'html' => SlotUtility::renderEmpty($object->targetFeedId, $canEditQueue)
            );
            echo ObjectHelper::ToJSON($result);
        }
    }
?>