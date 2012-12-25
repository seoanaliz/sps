<?php
/**
 * User: x100up
 * Date: 18.12.12 22:51
 * In Code We Trust
 */

Package::Load('SPS.Site/base');

class UserUserGroupFactory extends BaseModelFactory
{
    public static $mapping = array(
        'class' => 'UserUserGroup',
        'table' => 'userUserGroup',
        'view' => 'userUserGroup',
        'fields' => array(
            'vkId' => array(
                'name' => 'vkId',
                'type' => TYPE_INTEGER
            ),
            'userGroupId' => array(
                'name' => 'userGroupId',
                'type' => TYPE_INTEGER
            )
        ),
        'search' => array(
            'vkId' => array(
                'name' => 'vkId',
                'type' => TYPE_INTEGER
            ),
            'userGroupId' => array(
                'name' => 'userGroupId',
                'type' => TYPE_INTEGER
            ),
            'vkIdIn' => array(
                'name' => 'vkId',
                'type' => SEARCHTYPE_ARRAY
            ),
            'userGroupIdIn' => array(
                'name' => 'userGroupId',
                'type' => SEARCHTYPE_ARRAY
            ),
        ),
    );
}
