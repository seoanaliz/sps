<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.10.12
 * Time: 18:10
 * To change this template use File | Settings | File Templates.
 */
class GroupFactory
{
    /** Default Connection Name */
    const DefaultConnection = 'tst';

    /** Group instance mapping  */
    public static $mapping = array (
    'class'       => 'Group'
    , 'table'     => 'groups'
    , 'view'      => 'get_groups'
    , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => false )
    , 'cacheDeps' => array()
    , 'fields'    => array(
        'group_id' => array(
              'name'        => 'group_id'
            , 'type'        => TYPE_INTEGER
            , 'key'         => true
        )
        ,'name' => array(
              'name'        => 'name'
            , 'type'        => TYPE_STRING
            , 'nullable'    => 'CheckEmpty'
            , 'max'         => 255
        )
        ,'general' => array(
          'name'            => 'general'
        , 'type'            => TYPE_BOOLEAN
        )
        ,'type' => array(
              'name'        => 'type'
            , 'type'        => TYPE_INTEGER
        )
        ,'users_ids' => array(
              'name'        => 'users_ids'
            , 'type'        => TYPE_ARRAY
            , 'complexType' => 'int[]'
        )
        ,'created_by' => array(
              'name'        => 'created_by'
            , 'type'        => TYPE_INTEGER
        )
        ,'status' => array(
              'name'        => 'status'
            , 'type'        => TYPE_INTEGER
        )
        ,'source' => array(
              'name'        => 'source'
            , 'type'        => TYPE_INTEGER
        )
    )
    , 'search'    => array(
        '_group_id' => array(
              'name'          => 'group_id'
            , 'type'          => TYPE_INTEGER
            , 'searchType'    => SEARCHTYPE_ARRAY
        )
        ,'_type' => array(
              'name'        => 'type'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_ARRAY
        )
        ,'_users_ids' => array(
              'name'        => 'users_ids'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_INTARRAY_CONTAINS
        )
        ,'_created_by' => array(
              'name'        => 'created_by'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_ARRAY
        )
        ,'_statusNE' => array(
              'name'        => 'status'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_NOT_EQUALS
        )
        ,'_source' => array(
              'name'        => 'source'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_ARRAY
        )
        ,'_users_ids_in_array' => array(
                'name'      => 'users_ids'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_INTARRAY_CONTAINS
            , 'complexType' => 'int[]'
        )
        ,'pageSize' => array(
              'name'         => 'pageSize'
            , 'type'       => TYPE_INTEGER
            , 'default'    => 1000
        )
    )
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

    /** @return Group[] */
    public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return Group */
    public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return Group */
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

    /** @return Group */
    public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
    }

}