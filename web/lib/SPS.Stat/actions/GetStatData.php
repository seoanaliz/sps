<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 28.05.13
 * Time: 13:29
 * To change this template use File | Settings | File Templates.
 */
class GetStatData extends BaseControl
{
    public function Execute()
    {
        $hasAccessToPrivateGroups   =   false;
        $canEditGlobalGroups        =   false;
        $rank = StatAuthority::STAT_ROLE_GUEST;

        if( $this->vkId ) {
            $hasAccessToPrivateGroups = StatAccessUtility::HasAccessToPrivateGroups( $this->vkId, Group::STAT_GROUP );
            $canEditGlobalGroups      = StatAccessUtility::CanEditGlobalGroups( $this->vkId, Group::STAT_GROUP );
            $rank                     = StatAccessUtility::GetRankInSource($this->vkId, Group::STAT_GROUP );
        }

        Response::setParameter('hasAccessToPrivateGroups', $hasAccessToPrivateGroups);
        Response::setParameter('canEditGlobalGroups', $canEditGlobalGroups);
        Response::setParameter('rank', ObjectHelper::ToJSON($rank));
        Response::setParameter('isAuthorized', $rank > StatAuthority::STAT_ROLE_GUEST);

        $requestData = Page::$RequestData;
        $slug = isset($requestData[1]) ? $requestData[1] : null;
        
        if ($slug === 'my' && $rank === StatAuthority::STAT_ROLE_GUEST) {
            Response::setString('redirect', '/stat/'.$slug);
            return 'login'; // redirect
        }

        $EntryGetter = new EntryGetter();
        $id = null;
        if ($slug) {
            if ($slug === 'my' || $slug === 'not_listed') {
                $id = $slug;
            } else {
                $id = $EntryGetter->getGroupIdBySlug($slug);
                if (!$id) { // несуществующий URI
                    return 'default'; // redirect
                }
            }
        }

        Request::setString('groupId', $id); // Нужно, т.к. EntryGetter зависит от глобального состояния (Request)
        Response::setString('entriesPrecache', ObjectHelper::ToJSON($EntryGetter->getEntriesData()));

        include __DIR__ . '/controls/groups/getGroupList.php';
        $gl = new getGroupList();
        ob_start();
        $gl->Execute();
        $json = ob_get_clean();
        Response::setString('groupsPrecache', $json);
    }
}
