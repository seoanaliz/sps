<?php
Package::Load('SPS.Site/base');

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
            echo ObjectHelper::ToJSON($result);
            return;
        }

        $vkId = Request::getInteger('vkId');
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
            $object->targetFeedIds = array($targetFeedId);
            $result['success'] = AuthorFactory::Add($object);
        } else {
            //update
            if ($exists->statusId == 1) {
                $exists->targetFeedIds = !empty($exists->targetFeedIds) ? $exists->targetFeedIds : array();
                $exists->targetFeedIds = array_merge($exists->targetFeedIds, array($targetFeedId));
            } else {
                $exists->targetFeedIds = array($targetFeedId);
            }

            $exists->statusId = 1;

            $result['success'] = AuthorFactory::UpdateByMask($exists, array('targetFeedIds', 'statusId'), array('vkId' => $exists->vkId));
        }

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