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

        $vkId = Request::getInteger('vkId');
        $Author = new Author();
        $Author->statusId = 1;
        $Author->vkId = $vkId;
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $targetFeedId = Request::getInteger('targetFeedId');


        try {
            if (!empty($vkId)) {
                $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $vkId, 'fields' => 'photo'));
                $profile = current($profiles);
                $Author->firstName = $profile['first_name'];
                $Author->lastName = $profile['last_name'];
                $Author->avatar = $profile['photo'];
            }
        } catch (Exception $Ex) {
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        AuthorFactory::$mapping['view'] = 'authors';
        $exists = AuthorFactory::GetOne(array('vkId' => $vkId), array(BaseFactory::WithoutDisabled => false));
        $vkId = Request::getInteger('vkId');
        $Author = new Author();
        $Author->statusId = 1;
        $Author->vkId = $vkId;


        // copy to editor
        $Editor = EditorFactory::GetOne(array('vkId' => $vkId));
        if (!$Editor) {
            $Editor = new Editor();
        }
        $Editor->vkId = $vkId;
        $Editor->lastName = $Author->lastName;
        $Editor->firstName = $Author->firstName;
        $Editor->avatar = $Author->avatar;
        $Editor->statusId = $Author->statusId;
        if ($Editor->editorId){
            EditorFactory::Update($Editor);
        } else {
            EditorFactory::Add($Editor);
        }

        if (empty($exists)) {
            $result['success'] = AuthorFactory::Add($Author);
        } else {
            $UserFeed = new UserFeed();
            $UserFeed->vkId = $vkId;
            $UserFeed->role = UserFeed::ROLE_AUTHOR;
            $UserFeed->targetFeedId = $targetFeedId;
            UserFeedFactory::Add($UserFeed);
            $result['success'] = AuthorFactory::UpdateByMask($exists, array('statusId'), array('vkId' => $exists->vkId));
        }

        $manageEvent = new AuthorManage();
        $manageEvent->createdAt = DateTimeWrapper::Now();
        $manageEvent->authorVkId = $vkId;
        $manageEvent->editorVkId = AuthUtility::GetCurrentUser('Editor')->vkId;
        $manageEvent->action = 'add';
        $manageEvent->targetFeedId = $targetFeedId;
        AuthorManageFactory::Add($manageEvent);

        // copy to editor
        $Editor = EditorFactory::GetOne(array('vkId' => $vkId));
        if (!$Editor) {
            $Editor = new Editor();
        }
        $Editor->vkId = $vkId;
        $Editor->lastName = $Author->lastName;
        $Editor->firstName = $Author->firstName;
        $Editor->avatar = $Author->avatar;
        $Editor->statusId = $Author->statusId;
        if ($Editor->editorId) {
            EditorFactory::Update($Editor);
        } else {
            EditorFactory::Add($Editor);
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
}

?>