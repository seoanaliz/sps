<?php
Package::Load('SPS.Site/base');
/**
 * User: x100up
 * Date: 18.12.12 22:37
 * In Code We Trust
 */
class AddUserToUserGroup extends BaseControl
{
    public function Execute(){
        $userGroupId = Request::getInteger('userGroupId');
        $vkId = Request::getInteger('vkId');

        $UserUserGroup = new UserUserGroup();
        $UserUserGroup->vkId = $vkId;
        $UserUserGroup->userGroupId = $userGroupId;

        if (UserUserGroupFactory::Add($UserUserGroup)) {
            echo ObjectHelper::ToJSON(array('success' => true));
            return;
        }
        echo ObjectHelper::ToJSON(array('success' => false));
    }
}
