<?php
Package::Load('SPS.Site/base');
/**
 * User: x100up
 * Date: 18.12.12 22:37
 * In Code We Trust
 */
class RemoveUserFromUserGroup extends BaseControl
{
    public function Execute(){
        $userGroupId = Request::getInteger('userGroupId');
        $vkId = Request::getInteger('vkId');

        $UserGroup = UserGroupFactory::GetById($userGroupId);
        if (!$UserGroup) {
           return array('success' => false);
        }
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($UserGroup->targetFeedId);
        if (!is_null($role) && $role != UserFeed::ROLE_AUTHOR) {
            UserUserGroupFactory::DeleteByMask(array('vkId' => $vkId, 'userGroupId' => $userGroupId));
            return array('success' => true);
        }

        return array('success' => false);
    }
}
