<?php

/**
 * VkPublic Factory
 *
 */
class VkPublicFactory implements IFactory {

    /** Default Connection Name */
    const DefaultConnection = 'tst';

    /** VkPublic instance mapping  */
    public static $mapping = array (
      'class'     => 'VkPublic'
    , 'table'     => 'stat_publics_50k'
    , 'view'      => 'get_publics'
    , 'flags'     => array( 'CanPages' => 'CanPages' )
    , 'cacheDeps' => array()
    , 'fields'    => array(
            'vk_id' => array(
                'name'          => 'vk_id'
            , 'type'        => TYPE_INTEGER
            , 'nullable'    => 'No'
            )
        ,'ava' => array(
                'name'          => 'ava'
            , 'type'        => TYPE_STRING
            , 'max'         => 300
            )
        ,'name' => array(
                'name'          => 'name'
            , 'type'        => TYPE_STRING
            , 'max'         => 280
            )
        ,'short_name' => array(
              'name'        => 'short_name'
            , 'type'        => TYPE_STRING
            , 'max'         => 50
            )
        ,'diff_abs' => array(
              'name'        => 'diff_abs'
            , 'type'        => TYPE_INTEGER
            )
        ,'diff_rel' => array(
              'name'        => 'diff_rel'
            , 'type'        => TYPE_FLOAT
            )
        ,'quantity' => array(
              'name'        => 'quantity'
            , 'type'        => TYPE_INTEGER
            )
        ,'sh_in_main' => array(
              'name'        => 'sh_in_main'
            , 'type'        => TYPE_BOOLEAN
            )
        ,'diff_abs_week' => array(
              'name'        => 'diff_abs_week'
            , 'type'        => TYPE_INTEGER
            )
        ,'diff_abs_month' => array(
              'name'        => 'diff_abs_month'
            , 'type'        => TYPE_INTEGER
            )
        ,'diff_rel_week' => array(
              'name'        => 'diff_rel_week'
            , 'type'        => TYPE_FLOAT
            )
        ,'diff_rel_month' => array(
              'name'        => 'diff_rel_month'
            , 'type'        => TYPE_FLOAT
            )
        ,'is_page' => array(
              'name'        => 'is_page'
            , 'type'        => TYPE_BOOLEAN
            )
        ,'visitors' => array(
              'name'        => 'visitors'
            , 'type'        => TYPE_INTEGER
            )
        ,'in_search' => array(
              'name'        => 'in_search'
            , 'type'        => TYPE_BOOLEAN
            )
        ,'closed' => array(
              'name'        => 'closed'
            , 'type'        => TYPE_BOOLEAN
            )
        ,'active' => array(
              'name'        => 'active'
            , 'type'        => TYPE_BOOLEAN
            )
        ,'visitors_week' => array(
              'name'        => 'visitors_week'
            , 'type'        => TYPE_INTEGER
            )
        ,'visitors_month' => array(
              'name'        => 'visitors_month'
            , 'type'        => TYPE_INTEGER
            )
        ,'updated_at' => array(
              'name'        => 'updated_at'
            , 'type'        => TYPE_DATETIME
            )
        ,'vk_public_id' => array(
              'name'        => 'vk_public_id'
            , 'type'        => TYPE_INTEGER
            , 'key'         => true
        )
        ,'viewers' => array(
              'name'        => 'viewers'
            , 'type'        => TYPE_INTEGER
        )
        ,'viewers_week' => array(
              'name'        => 'viewers_week'
            , 'type'        => TYPE_INTEGER
        )
        ,'viewers_month' => array(
              'name'        => 'viewers_month'
            , 'type'        => TYPE_INTEGER
        ),'inLists' => array(
              'name'        => 'viewers_month'
            , 'type'        => TYPE_BOOLEAN
        ),'cpp' => array(
             'name'        => 'cpp',
             'type'        => TYPE_INTEGER
        ),
        'cppChange' => array(
             'name'        => 'cppChange',
             'type'        => TYPE_STRING
        )
    )
    , 'lists'     => array()
    , 'search'    => array(
            'page' => array(
                '  name'       => 'page'
                , 'type'       => TYPE_INTEGER
                , 'default'    => 0
            )
            ,'pageSize' => array(
                  'name'       => 'pageSize'
                , 'type'       => TYPE_INTEGER
                , 'default'    => 25
            )
            ,'_vk_public_id' => array(
                  'name'         => 'vk_public_id'
                , 'type'         => TYPE_INTEGER
                , 'searchType'   => SEARCHTYPE_ARRAY
            )
            ,'_vk_id' => array(
                  'name'         => 'vk_id'
                , 'type'         => TYPE_STRING
                , 'searchType'   => SEARCHTYPE_ARRAY
            )
            ,'_quantityLE' => array(
                  'name'         => 'quantity'
                , 'type'         => TYPE_INTEGER
                , 'searchType'   => SEARCHTYPE_LE
            )
            ,'_quantityGE' => array(
                  'name'         => 'quantity'
                , 'type'         => TYPE_INTEGER
                , 'searchType'   => SEARCHTYPE_GE
            )
            ,'_nameIL'     => array(
                  'name'         => 'name'
                , 'type'         => TYPE_STRING
                , 'searchType'   => SEARCHTYPE_ILIKE
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

    /** @return VkPublic[] */
    public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return VkPublic */
    public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return VkPublic */
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

    /** @return VkPublic */
    public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
    }

}
?>