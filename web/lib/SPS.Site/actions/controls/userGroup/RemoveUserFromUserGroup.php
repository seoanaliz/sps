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

        UserUserGroupFactory::DeleteByMask(array('vkId' => $vkId, 'userGroupId' => $userGroupId));
    }
}
