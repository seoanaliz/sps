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
        $canSuggestGlobalGroups     =   false;
        $rank = StatAuthority::STAT_ROLE_GUEST;

        if( $this->vkId ) {
            $hasAccessToPrivateGroups = StatAccessUtility::HasAccessToPrivateGroups( $this->vkId, Group::STAT_GROUP );
            $canEditGlobalGroups      = StatAccessUtility::CanEditGlobalGroups( $this->vkId, Group::STAT_GROUP );
            $rank                     = StatAccessUtility::GetRankInSource($this->vkId, Group::STAT_GROUP );
        }

        Response::setParameter('hasAccessToPrivateGroups', $hasAccessToPrivateGroups);
        Response::setParameter('canEditGlobalGroups', $canEditGlobalGroups);
        Response::setParameter('canSuggestGlobalGroups', $canSuggestGlobalGroups);
        Response::setParameter('rank', $rank);
    }
}
