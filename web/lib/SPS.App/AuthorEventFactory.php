<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.App' );

    /**
     * AuthorEvent Factory
     *
     * @package SPS
     * @subpackage App
     */
    class AuthorEventFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** AuthorEvent instance mapping  */
        public static $mapping = array (
            'class'       => 'AuthorEvent'
            , 'table'     => 'authorEvents'
            , 'view'      => 'getAuthorEvents'
            , 'flags'     => array( 'CanCache' => false, 'WithoutTemplates' => 'WithoutTemplates', 'AddablePK' => 'AddablePK' )
            , 'cacheDeps' => array( 'authors', 'articles' )
            , 'fields'    => array(
                'articleId' => array(
                    'name'          => 'articleId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'key'         => true
                    , 'foreignKey'  => 'Article'
                )
                ,'authorId' => array(
                    'name'          => 'authorId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Author'
                )
                ,'commentIds' => array(
                    'name'          => 'commentIds'
                    , 'type'        => TYPE_ARRAY
                    , 'complexType' => 'int[]'
                )
                ,'isQueued' => array(
                    'name'          => 'isQueued'
                    , 'type'        => TYPE_BOOLEAN
                    , 'nullable'    => 'No'
                )
                ,'isSent' => array(
                    'name'          => 'isSent'
                    , 'type'        => TYPE_BOOLEAN
                    , 'nullable'    => 'No'
                ))
            , 'lists'     => array()
            , 'search'    => array(
                '_articleId' => array(
                    'name'         => 'articleId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
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

        /** @return AuthorEvent[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return AuthorEvent */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return AuthorEvent */
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

        /** @return AuthorEvent */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>