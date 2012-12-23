<?php
    /**
     * AddAuthorControl Action
     * @package    SPS
     * @subpackage Site
     * @author     shuler
     */
    class AddAuthorControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array('success' => false);
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $targetFeedId = Request::getInteger('targetFeedId');
            if (!$TargetFeedAccessUtility->canAddAuthor($targetFeedId)) {
                Logger::Debug('Add Author access denied');
                echo ObjectHelper::ToJSON($result);
                return;
            }

            $vkId = Request::getInteger( 'vkId' );
            $object = new Author();
            $object->statusId = 1;
            $object->vkId = $vkId;

            try {
                if (!empty($vkId)) {
                    $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $vkId, 'fields' => 'photo'));
                    $profile = current($profiles);
                    $object->firstName = $profile['first_name'];
                    $object->lastName = $profile['last_name'];
                    $object->avatar = $profile['photo'];
                }
            } catch (Exception $Ex) {
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            AuthorFactory::$mapping['view'] = 'authors';
            $exists = AuthorFactory::GetOne(array('vkId' => $vkId), array(BaseFactory::WithoutDisabled => false));

            if (empty($exists)) {
                $result['success'] = AuthorFactory::Add($object);
            } else {
                $exists->statusId = 1;

                $result['success'] = AuthorFactory::UpdateByMask($exists, array('statusId'), array('vkId' => $exists->vkId));
            }

            $UserFeed = new UserFeed();
            $UserFeed->vkId = $vkId;
            $UserFeed->role = UserFeed::ROLE_AUTHOR;
            $UserFeed->targetFeedId = $targetFeedId;
            UserFeedFactory::Add($UserFeed);

            $manageEvent = new AuthorManage();
            $manageEvent->createdAt = DateTimeWrapper::Now();
            $manageEvent->authorVkId = $vkId;
            $manageEvent->editorVkId = AuthUtility::GetCurrentUser('Editor')->vkId;
            $manageEvent->action = 'add';
            $manageEvent->targetFeedId = $targetFeedId;
            AuthorManageFactory::Add($manageEvent);

            echo ObjectHelper::ToJSON($result);
        }
    }

?>