<?php
/**
 * User: x100up
 * Date: 16.12.12 17:18
 * In Code We Trust
 */
abstract class BaseModelFactory implements IFactory {
    /** Default Connection Name */
    const DefaultConnection = null;

    /** UserFeed instance mapping  */
    public static $mapping = array();

    /**
     * @param $object
     * @param null $options
     * @param null $connectionName
     * @return array
     */
    public static function Validate($object, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Validate($object, self::$mapping, $options, $connectionName);
    }

    /**
     * @param $search
     * @param null $options
     * @param null $connectionName
     * @return array
     */
    public static function ValidateSearch($search, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::ValidateSearch($search, self::$mapping, $options, $connectionName);
    }

    /**
     * @param $object
     * @param $changes
     * @param null $searchArray
     * @param null $connectionName
     * @return bool
     */
    public static function UpdateByMask($object, $changes, $searchArray = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::UpdateByMask($object, $changes, $searchArray, self::$mapping, $connectionName);
    }

    /**
     * @param $objects
     * @param null $originalObjects
     * @param null $connectionName
     * @return array
     */
    public static function SaveArray($objects, $originalObjects = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::SaveArray($objects, $originalObjects, self::$mapping, $connectionName);
    }

    /**
     * @return bool
     */
    public static function CanPages()
    {
        return BaseFactory::CanPages(self::$mapping);
    }


    public static function Add($object, $options = null, $connectionName = self::DefaultConnection)
    {
        return BaseFactory::Add($object, self::$mapping, $options, $connectionName);
    }

    /**
     * @param $objects
     * @param null $options
     * @param null $connectionName
     * @return bool
     */
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

    /**
     * @param null $searchArray
     * @param null $options
     * @param null $connectionName
     * @return array
     */
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
}
