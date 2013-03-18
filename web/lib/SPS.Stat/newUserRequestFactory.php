<?php


Package::Load( 'tst.' );

/**
 * NewUserRequest Factory
 *
 * @package SPS
 * @subpackage Stat
 */
class NewUserRequestFactory implements IFactory {

    /** Default Connection Name */
    const DefaultConnection = 'tst';

    /** NewUserRequest instance mapping  */
    public static $mapping = array (
        'class'       => 'NewUserRequest'
    , 'table'     => 'newUserRequests'
    , 'view'      => 'getNewUserRequests'
    , 'flags'     => array( 'CanPages' => 'CanPages', 'WithoutTemplates' => 'WithoutTemplates' )
    , 'cacheDeps' => array()
    , 'fields'    => array(
            'newUserRequestId' => array(
                'name'          => 'newUserRequestId'
            , 'type'        => TYPE_INTEGER
            , 'key'         => true
            )
        ,'vkId' => array(
                'name'          => 'vkId'
            , 'type'        => TYPE_STRING
            )
        ,'email' => array(
                'name'          => 'email'
            , 'type'        => TYPE_STRING
            )
        ,'publicIds' => array(
                'name'          => 'publicIds'
            , 'type'        => TYPE_ARRAY
            , 'complexType' => 'int[]'
            )
        ,'statusId' => array(
                'name'          => 'statusId'
            , 'type'        => TYPE_INTEGER
            )
        ,'createdAt' => array(
                'name'          => 'createdAt'
            , 'type'        => TYPE_DATETIME
            ))
    , 'lists'     => array()
    , 'search'    => array(
            'page' => array(
                'name'         => 'page'
            , 'type'       => TYPE_INTEGER
            , 'default'    => 0
            )
        ,'pageSize' => array(
                'name'         => 'pageSize'
            , 'type'       => TYPE_INTEGER
            , 'default'    => 25
            ))
    );

    /** @return array */
    public static function Validate( $object, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Validate( $object, self::$mapping, $options, $connectionName );
    }

    /** @return array */
    public static function ValidateSearch( $search, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::ValidateSearch( $search, self::$mapping, $options, $connectionName );
    }

    /** @return bool|array */
    public static function UpdateByMask( $object, $changes, $searchArray = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::UpdateByMask( $object, $changes, $searchArray, self::$mapping, $connectionName );
    }

    public static function SaveArray( $objects, $originalObjects = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::SaveArray( $objects, $originalObjects, self::$mapping, $connectionName );
    }

    public static function CanPages() {
        return BaseFactory::CanPages( self::$mapping );
    }

    /** @return bool|array */
    public static function Add( $object, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Add( $object, self::$mapping, $options, $connectionName );
    }

    /** @return bool */
    public static function AddRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::AddRange( $objects, self::$mapping, $options, $connectionName );
    }

    /** @return bool|array */
    public static function Update( $object, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Update( $object, self::$mapping, $options, $connectionName );
    }

    /** @return bool */
    public static function UpdateRange( $objects, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::UpdateRange( $objects, self::$mapping, $options, $connectionName );
    }

    public static function Count( $searchArray, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Count( $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return NewUserRequest[] */
    public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return NewUserRequest */
    public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return NewUserRequest */
    public static function GetOne( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetOne( $searchArray, self::$mapping, $options, $connectionName );
    }

    public static function GetCurrentId( $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetCurrentId( self::$mapping, $connectionName );
    }

    public static function Delete( $object, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Delete( $object, self::$mapping, $connectionName );
    }

    public static function DeleteByMask( $searchArray, $connectionName = self::DefaultConnection ) {
        return BaseFactory::DeleteByMask( $searchArray, self::$mapping, $connectionName );
    }

    public static function PhysicalDelete( $object, $connectionName = self::DefaultConnection ) {
        return BaseFactory::PhysicalDelete( $object, self::$mapping, $connectionName );
    }

    public static function LogicalDelete( $object, $connectionName = self::DefaultConnection ) {
        return BaseFactory::LogicalDelete( $object, self::$mapping, $connectionName );
    }

    /** @return NewUserRequest */
    public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
    }

}
?>