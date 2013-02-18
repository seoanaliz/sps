<?php
/**
 * Возвращает список авторов для ленты
 * @package    SPS
 * @subpackage Site
 * @author     shuler
 */
class GetAuthorsListControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $targetFeedId = Request::getInteger('targetFeedId');

        if (!$TargetFeedAccessUtility->canShowAuthorList($targetFeedId)) {
            return;
        }

        $authors = $authorGroups = $UserGroups = array();

        if (!empty($targetFeedId)) {
            $UserGroups = UserGroupFactory::Get(array('targetFeedId' => $targetFeedId));
            $UserFeeds = UserFeedFactory::Get(array('targetFeedId' => $targetFeedId, 'role' => UserFeed::ROLE_AUTHOR));
            if ($UserFeeds) {
                $vkIds = array();
                foreach ($UserFeeds as $UserFeed) {
                    $vkIds[] = $UserFeed->vkId;
                }

                $authors = AuthorFactory::Get(
                    array(
                        'vkIdIn' => $vkIds
                    )
                    , array(
                        BaseFactory::WithoutPages => true,
                        BaseFactory::OrderBy => ' "firstName", "lastName" ',
                    )
                );

                foreach ($authors as $author) {
                    $authorGroups[$author->vkId] = array();
                }

                if ($authorGroups) {

                    if ($UserGroups) {
                        $userGroupIds = array();
                        foreach ($UserGroups as $UserGroup) {
                            $userGroupIds[] = $UserGroup->userGroupId;
                        }
                        $UserUserGroups = UserUserGroupFactory::Get(array('vkIdIn' => array_keys($authorGroups), 'userGroupIdIn' => $userGroupIds));
                        if ($UserUserGroups) {
                            foreach ($UserUserGroups as $UserUserGroup) {
                                $authorGroups[$UserUserGroup->vkId][] = $UserUserGroup->userGroupId;
                            }
                        }
                    }
                }
            }
        }

        Response::setArray('authors', $authors);
        Response::setArray('authorGroups', $authorGroups);
        Response::setArray('userGroups', $UserGroups);
    }
}

?>