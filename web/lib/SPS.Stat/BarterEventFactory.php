<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.10.12
 * Time: 18:10
 * To change this template use File | Settings | File Templates.
 */
class BarterEventFactory
{
    /** Default Connection Name */
    const DefaultConnection = 'tst';

    /** BarterEvent instance mapping  */
    public static $mapping = array (
    'class'       => 'BarterEvent'
    , 'table'     => 'barter_events'
    , 'view'      => 'get_barter_events'
    , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => false )
    , 'cacheDeps' => array()
    , 'fields'    => array(
        'barter_event_id' => array(
              'name'        => 'barter_event_id'
            , 'type'        => TYPE_INTEGER
            , 'key'         => true
            )
        ,'barter_type' => array(
          'name'          => 'barter_type'
        , 'type'        => TYPE_INTEGER
        , 'nullable'    => 'CheckEmpty'
        )
        ,'status' => array(
          'name'          => 'status'
        , 'type'        => TYPE_INTEGER
        , 'nullable'    => 'CheckEmpty'
        )
        ,'barter_public' => array(
              'name'        => 'barter_public'
            , 'type'        => TYPE_STRING
            , 'max'         => 255
            , 'nullable'    => 'CheckEmpty'
        )
        ,'target_public' => array(
              'name'        => 'target_public'
            , 'type'        => TYPE_STRING
            , 'max'         => 255
            , 'nullable'    => 'CheckEmpty'
        )
        ,'search_string' => array(
          'name'          => 'search_string'
        , 'type'        => TYPE_STRING
        , 'max'         => 255
        , 'nullable'    => 'CheckEmpty'
        )
        ,'start_search_at' => array(
          'name' => 'start_search_at'
        , 'type' => TYPE_DATETIME
        )
        ,'stop_search_at' => array(
          'name' => 'stop_search_at'
        , 'type' => TYPE_DATETIME
        )
        ,'posted_at' => array(
          'name'          => 'posted_at'
        , 'type'        => TYPE_DATETIME
        )
        ,'deleted_at' => array(
          'name'          => 'deleted_at'
        , 'type'        => TYPE_DATETIME
        )
        ,'detected_at' => array(
            'name' => 'detected_at'
           ,'type' => TYPE_DATETIME
        )
        ,'barter_overlaps' => array(
          'name'        => 'barter_overlaps'
        , 'type'        => TYPE_STRING
        )
        ,'start_visitors'   => array(
              'name'        => 'start_visiters'
            , 'type'        => TYPE_INTEGER
            )
        ,'end_visitors'     => array(
              'name'        => 'end_visiters'
            , 'type'        => TYPE_INTEGER
            )
        ,'start_subscribers' => array(
          'name'        => 'start_subscribers'
        , 'type'        => TYPE_INTEGER
        )
        ,'end_subscribers' => array(
          'name'        => 'end_subscribers'
        , 'type'        => TYPE_INTEGER
        )
        ,'created_at' => array(
                'name'      => 'start_search_at'
            , 'type'        => TYPE_DATETIME
        )
        ,'post_id' => array(
                'name'      => 'start_search_at'
            , 'type'        => TYPE_STRING
        )
        ,'standard_mark'  => array(
              'name'      => 'standard_mark'
            , 'type'      => TYPE_BOOLEAN
        )
        ,'groups_ids' => array(
              'name'        => 'groups_ids'
            , 'type'        => TYPE_ARRAY
            , 'complexType' => 'int[]'
        )
        ,'creator_id' => array(
              'name'        => 'creator_id'
            , 'type'        => TYPE_STRING
        )
        ,'init_users' => array(
              'name'        => 'init_users'
            , 'type'        => TYPE_ARRAY
            , 'complexType' => 'int[]'
        )
        ,'neater_subscribers' => array(
              'name'        => 'neater_subscribers'
            , 'type'        => TYPE_INTEGER
         )
        ,'skiped_subscribers' => array(
              'name'        => 'skiped_subscribers'
            , 'type'        => TYPE_INTEGER
        )
    )
    , 'search'    => array(
            '_barter_event_id' => array(
                'name'        => 'barter_event_id'
            , 'type'          => TYPE_INTEGER
            , 'searchType'    => SEARCHTYPE_ARRAY
            )
        ,'_barter_type' => array(
              'name'        => 'barter_type'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_ARRAY
            )
        ,'_status' => array(
            'name'          => 'status'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_ARRAY
            )
        ,'_barter_public' => array(
            'name'          => 'barter_public'
            , 'type'        => TYPE_STRING
            , 'searchType'  => SEARCHTYPE_ARRAY
            )
        ,'_search_string' => array(
            'name'          => 'search_string'
            , 'type'        => TYPE_STRING
            , 'searchType'  => SEARCHTYPE_ARRAY
            )
        ,'_target_public' => array(
              'name'        => 'target_public'
            , 'type'        => TYPE_STRING
            , 'searchType'  => SEARCHTYPE_ARRAY
            )
        ,'page' => array(
              'name'         => 'page'
            , 'type'       => TYPE_INTEGER
            , 'default'    => 0
            )
        ,'pageSize' => array(
              'name'         => 'pageSize'
            , 'type'       => TYPE_INTEGER
            , 'default'    => 1000
            )
        ,'_start_search_atLE' => array(
              'name'         => 'start_search_at'
            , 'type'         => TYPE_DATETIME
            ,'searchType'    => SEARCHTYPE_LE
        )
        ,'_posted_atLE' => array(
             'name'         => 'posted_at'
            ,'type'         => TYPE_DATETIME
            ,'searchType'   => SEARCHTYPE_LE
        )
        ,'_posted_atGE' => array(
             'name'         => 'posted_at'
            ,'type'         => TYPE_DATETIME
            ,'searchType'   => SEARCHTYPE_GE
        )

        ,'_start_search_atGE' => array(
             'name'         => 'start_search_at'
            ,'type'         => TYPE_DATETIME
            ,'searchType'    => SEARCHTYPE_GE
        )
        ,'_stop_search_atGE' => array(
              'name'         => 'stop_search_at'
            , 'type'         => TYPE_DATETIME
            , 'searchType'   => SEARCHTYPE_GE
        )
        ,'_stop_search_atLE' => array(
              'name'         => 'stop_search_at'
            , 'type'         => TYPE_DATETIME
            , 'searchType'   => SEARCHTYPE_LE
        )
        ,'_created_atGE' => array(
              'name'         => 'created_at'
            , 'type'         => TYPE_DATETIME
            , 'searchType'   => SEARCHTYPE_GE
        )
        ,
        '_statusNE' => array(
              'name'        => 'status'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_NOT_EQUALS
        )
        ,
        '_standard_markE' => array(
              'name'        => 'standard_mark'
            , 'type'        => TYPE_BOOLEAN
            , 'searchType'  => SEARCHTYPE_EQUALS
        )
        ,'_groups_ids' => array(
              'name'        => 'groups_ids'
            , 'type'        => TYPE_INTEGER
            , 'searchType'  => SEARCHTYPE_INTARRAY_CONTAINS
            , 'complexType' => 'int[]'
        )
    ));

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

    /** @return BarterEvent[] */
    public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return BarterEvent */
    public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
    }

    /** @return BarterEvent */
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

    /** @return BarterEvent */
    public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
        return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
    }

}