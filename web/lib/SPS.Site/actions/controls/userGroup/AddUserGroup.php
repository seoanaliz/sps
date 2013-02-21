<?php
Package::Load('SPS.Site/base');
/**
 * User: x100up
 * Date: 16.12.12 17:12
 * In Code We Trust
 */
class AddUserGroup extends BaseControl {
    public function Execute(){
        $result = array('success' => false);

        $targetFeedId = Request::getInteger('targetFeedId');
        $name = Request::getString('name');

        if (empty($name)) {
            $result['message'] = 'Empty name';
            echo ObjectHelper::ToJSON($result);
            return;
        }

        if (empty($targetFeedId)) {
            $result['message'] = 'Empty public Id';
            echo ObjectHelper::ToJSON($result);
            return;
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        if (!$TargetFeedAccessUtility->canAddUserGroup($targetFeedId)) {
            $result['message'] = 'Access denied';
            echo ObjectHelper::ToJSON($result);
            return;
        }

        $UserGroup = new UserGroup();
        $UserGroup->name = $name;
        $UserGroup->targetFeedId = $targetFeedId;
        $addResult = UserGroupFactory::Add($UserGroup, array(BaseFactory::WithReturningKeys => true));

        if ($addResult) {
            $result = array(
                'success' => true,
                'userGroup' => $UserGroup->toArray(),
            );
            echo ObjectHelper::ToJSON($result);
        } else {
            $result['message'] = 'Add error';
            echo ObjectHelper::ToJSON($result);
        }
    }
}
