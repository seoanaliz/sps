<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'stat.' );

    /**
     * StatUser Factory
     *
     * @package stat
     * @subpackage 
     */
    class StatUserFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = 'tst';

        /** StatUser instance mapping  */
        public static $mapping = array (
            'class'       => 'StatUser'
            , 'table'     => 'stat_users'
            , 'view'      => 'getStat_users'
            , 'flags'     => array(  )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'user_id' => array(
                    'name'          => 'user_id'
                    , 'type'        => TYPE_INTEGER
                )
                ,'name' => array(
                    'name'          => 'name'
                    , 'type'        => TYPE_STRING
                )
                ,'ava' => array(
                    'name'          => 'ava'
                    , 'type'        => TYPE_STRING
                )
                ,'comments' => array(
                    'name'          => 'comments'
                    , 'type'        => TYPE_STRING
                )
                ,'rank' => array(
                    'name'          => 'rank'
                    , 'type'        => TYPE_STRING
                )
                ,'access_token' => array(
                    'name'          => 'access_token'
                    , 'type'        => TYPE_STRING
                )
                ,'mes_block_ts' => array(
                    'name'          => 'mes_block_ts'
                    , 'type'        => TYPE_INTEGER
                )
                ,'status' => array(
                    'name'          => 'status'
                    , 'type'        => TYPE_INTEGER
                )
                ,'groups_ids' => array(
                    'name'          => 'groups_ids'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'int[]'
                )
                ,'id' => array(
                      'name'        => 'id'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'No'
                    , 'key'         => true
                )
            )
            , 'lists'     => array()
            , 'search'    => array(
                'groups_idsC' => array(
                      'name'        => 'groups_ids'
                    , 'type'        => TYPE_INTEGER
                    , 'searchType'  => SEARCHTYPE_INTARRAY_CONTAINS
                    , 'complexType' => 'int[]'
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

        /** @return StatUser[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return StatUser */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return StatUser */
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

        /** @return StatUser */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>