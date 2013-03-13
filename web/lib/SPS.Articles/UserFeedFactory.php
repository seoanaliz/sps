<?php

Package::Load('SPS.Articles');

/**
 * UserFeed Factory
 *
 * @package SPS
 * @subpackage Articles
 */
class UserFeedFactory implements IFactory
{

    /** Default Connection Name */
    const DefaultConnection = null;

    /** UserFeed instance mapping  */
    public static $mapping = array(
        'class' => 'UserFeed',
        'table' => 'userFeed',
        'view' => 'userFeed',
        //, 'flags'     => array( 'CanCache' => 'CanCache' )
        //, 'cacheDeps' => array()
        'fields' => array(
            'vkId' => array(
                'name' => 'vkId'
            , 'type' => TYPE_INTEGER
            ),
            'targetFeedId' => array(
                'name' => 'targetFeedId',
                'type' => TYPE_INTEGER
            ),
            'role' => array(
                'name' => 'role',
                'type' => TYPE_INTEGER
            )
        )
    );

    /** @return array */
    public static function Validate($object, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Validate($object, self::$mapping, $options, $connectionName);
    }

    /** @return array */
    public static function ValidateSearch($search, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::ValidateSearch($search, self::$mapping, $options, $connectionName);
    }

    /** @return bool|array */
    public static function UpdateByMask($object, $changes, $searchArray = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::UpdateByMask($object, $changes, $searchArray, self::$mapping, $connectionName);
    }

    public static function SaveArray($objects, $originalObjects = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::SaveArray($objects, $originalObjects, self::$mapping, $connectionName);
    }

    public static function CanPages()
    {
        return BaseFactory::CanPages(self::$mapping);
    }

    /** @return bool|array */
    public static function Add($object, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Add($object, self::$mapping, $options, $connectionName);
    }

    /** @return bool */
    public static function AddRange($objects, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::AddRange($objects, self::$mapping, $options, $connectionName);
    }

    /** @return bool|array */
    public static function Update($object, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Update($object, self::$mapping, $options, $connectionName);
    }

    /** @return bool */
    public static function UpdateRange($objects, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::UpdateRange($objects, self::$mapping, $options, $connectionName);
    }

    public static function Count($searchArray, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Count($searchArray, self::$mapping, $options, $connectionName);
    }

    /** @return UserFeed[] */
    public static function Get($searchArray = null, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Get($searchArray, self::$mapping, $options, $connectionName);
    }

    /** @return UserFeed */
    public static function GetById($id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::GetById($id, $searchArray, self::$mapping, $options, $connectionName);
    }

    /** @return UserFeed */
    public static function GetOne($searchArray = null, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::GetOne($searchArray, self::$mapping, $options, $connectionName);
    }

    public static function GetCurrentId($connectionName = self::DefaultConnection)
    {
        return BaseFactory::GetCurrentId(self::$mapping, $connectionName);
    }

    public static function Delete($object, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Delete($object, self::$mapping, $connectionName);
    }

    public static function DeleteByMask($searchArray, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::DeleteByMask($searchArray, self::$mapping, $connectionName);
    }

    public static function PhysicalDelete($object, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::PhysicalDelete($object, self::$mapping, $connectionName);
    }

    public static function LogicalDelete($object, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::LogicalDelete($object, self::$mapping, $connectionName);
    }

    /** @return UserFeed */
    public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
    }

    /**
     * Возвращает UserFeed -ы для $targetFeedId
     * array['role']['vkId'] = UserFeed
     * @param $targetFeedId
     * @return array
     */
    public static function GetForTargetFeed($targetFeedId)
    {
        if (!$targetFeedId) return array();
        $UserFeeds = BaseFactory::Get(array('targetFeedId' => $targetFeedId), self::$mapping, null, self::DefaultConnection);
        $result = array();
        foreach ($UserFeeds as $UserFeed) {
            /** @var $UserFeed UserFeed */
            if (!isset($result[$UserFeed->role])) {
                $result[$UserFeed->role] = array();
            }
            $result[$UserFeed->role][$UserFeed->vkId] = $UserFeed;
        }
        return $result;
    }

    /**
     * Возвращает UserFeed -ы для $targetFeedId
     * array['role'] = array(UserFeed)
     * @param $vkId
     * @param null $roleId
     * @return array
     */
    public static function GetForVkId($vkId, $roleId = null)
    {
        $searchArray = array('vkId' => $vkId);
        if (!is_null($roleId)) {
            $searchArray['role'] = $roleId;
        }
        $UserFeeds = BaseFactory::Get($searchArray, self::$mapping, null, self::DefaultConnection);
        $result = array();
        foreach ($UserFeeds as $UserFeed) {
            /** @var $UserFeed UserFeed */
            if (!isset($result[$UserFeed->role])) {
                $result[$UserFeed->role] = array();
            }
            $result[$UserFeed->role][] = $UserFeed;
        }
        return $result;
    }

    public static function DeleteForTargetFeed($targetFeedId){
        self::DeleteByMask(array('targetFeedId' => $targetFeedId));
    }

    public static function DeleteForVkId( $vkId, $roleId = null ) {
        self::DeleteByMask( array('vkId' => $vkId, 'role' => $roleId ));
    }

}

?>