<?php
/**
 * AddAuthorControl Action
 * @package    SPS
 * @subpackage Site
 * @author     shuler
 */
class AddAuthorControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {

        $result = array('success' => false);
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $targetFeedId = Request::getInteger('targetFeedId');

        if (!$TargetFeedAccessUtility->canAddAuthor($targetFeedId)) {
            Logger::Debug('Add Author access denied');
            echo ObjectHelper::ToJSON($result);
            return;
        }

        $vkId = Request::getInteger('vkId');
        AuthorFactory::$mapping['view'] = 'authors';
        $exists = AuthorFactory::GetOne(array('vkId' => $vkId), array(BaseFactory::WithoutDisabled => false));
        if ($exists){
            $Author = $exists;
        } else {
            $Author = new Author();
            $Author->vkId = $vkId;
        }
        $Author->statusId = 1;

        $targetFeedId = Request::getInteger('targetFeedId');

        try {
             $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $vkId, 'fields' => 'photo'));
             $profile = current($profiles);
             $Author->firstName = $profile['first_name'];
             $Author->lastName = $profile['last_name'];
             $Author->avatar = $profile['photo'];
        } catch (Exception $Ex) {
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        if (empty($exists)) {
            $result['success'] = AuthorFactory::Add($Author);
        } else {
            $result['success'] = AuthorFactory::UpdateByMask($Author, array('statusId', 'firstName', 'lastName', 'avatar'), array('vkId' => $Author->vkId));
        }

        if (!$result['success']){
            $result['message'] = 1;
            echo ObjectHelper::ToJSON($result);
            return;
        }

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

        // добавляем права автора
        $UserFeed = new UserFeed();
        $UserFeed->vkId = $vkId;
        $UserFeed->role = UserFeed::ROLE_AUTHOR;
        $UserFeed->targetFeedId = $targetFeedId;
        UserFeedFactory::Add($UserFeed);

        $manageEvent = new AuthorManage();
        $manageEvent->createdAt = DateTimeWrapper::Now();
        $manageEvent->authorVkId = $vkId;
        $manageEvent->editorVkId = $this->vkId;
        $manageEvent->action = 'add';
        $manageEvent->targetFeedId = $targetFeedId;
        AuthorManageFactory::Add($manageEvent);

        echo ObjectHelper::ToJSON($result);
    }
}

?>