<?php
/**
 * User: x100up
 * Date: 25.12.12 0:08
 * In Code We Trust
 */
class GetUserGroups extends BaseControl
{
    public function Execute()
    {
        $targetFeedId =  Request::getInteger('targetFeedId');
        if (!$targetFeedId){
            return array('success' => false);
        }

        $userGroups = UserGroupFactory::GetForUserTargetFeed($targetFeedId, $this->vkId);
        $showUserGroups = array();

        foreach ($userGroups as $userGroup) {
            /** @var $userGroup UserGroup */
            $showUserGroups[] = $userGroup->toArray();
        }

        echo ObjectHelper::ToJSON(array('userGroups' => $showUserGroups));
    }
}
