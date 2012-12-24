<?php
/**
 * User: x100up
 * Date: 16.12.12 13:50
 * In Code We Trust
 */

Package::Load('SPS.Site/base');

/**
 * @static UserGroup[] Get(array|null $searchArray=null, array|null $options=null, array|null $connectionName=null)
 * @static UserGroup GetById(array|null $searchArray=null, array|null $options=null, array|null $connectionName=null)
 * @static UserGroup GetOne(array|null $searchArray=null, array|null $options=null, array|null $connectionName=null)
 */
class UserGroupFactory extends BaseModelFactory {

    public static $mapping = array(
        'class' => 'UserGroup',
        'table' => 'userGroup',
        'view' => 'userGroup',
        'fields' => array(
            'userGroupId' => array(
                'name' => 'userGroupId',
                'type' => TYPE_INTEGER,
                'key'  => true
            ),
            'targetFeedId' => array(
                'name' => 'targetFeedId',
                'type' => TYPE_INTEGER
            ),
            'name' => array(
                'name' => 'name',
                'type' => TYPE_STRING
            ),
        ),
        'search' => array(
            'vkId' => array(
                'name'         => 'vkId',
                'type'       => TYPE_INTEGER,
            ),
            'targetFeedId' => array(
                'name'         => 'targetFeedId',
                'type'       => TYPE_INTEGER,
            ),
        )
    );


    public static function GetForTargetFeed($targetFeedId){
        return self::Get(array('targetFeedId' => $targetFeedId));
    }

    public static function GetForUserTargetFeed($targetFeedId, $vkId){
        return self::Get(array('targetFeedId' => $targetFeedId, 'vkId' => $vkId));
    }
}
