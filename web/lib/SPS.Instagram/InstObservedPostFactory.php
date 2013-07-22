<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Instagram' );

    /**
     * InstObservedPost Factory
     *
     * @package SPS
     * @subpackage Instagram
     */
    class InstObservedPostFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = 'tst';

        /** InstObservedPost instance mapping  */
        public static $mapping = array (
            'class'       => 'InstObservedPost'
            , 'table'     => 'inst_observed_posts'
            , 'view'      => 'getInst_observed_posts'
            , 'flags'     => array( 'WithoutTemplates' => 'WithoutTemplates', 'AddablePK' => 'AddablePK' )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'id' => array(
                    'name'          => 'id'
                    , 'type'        => TYPE_STRING
                    , 'key'         => true
                )
                ,'posted_at' => array(
                    'name'          => 'posted_at'
                    , 'type'        => TYPE_DATETIME
                )
                ,'reference_id' => array(
                    'name'          => 'reference_id'
                    , 'type'        => TYPE_STRING
                )
                ,'likes' => array(
                    'name'          => 'likes'
                    , 'type'        => TYPE_INTEGER
                )
                ,'comments' => array(
                    'name'          => 'comments'
                    , 'type'        => TYPE_INTEGER
                )
                ,'ref_start_subs' => array(
                    'name'          => 'ref_start_subs'
                    , 'type'        => TYPE_INTEGER
                )
                ,'ref_end_subs' => array(
                    'name'          => 'ref_end_subs'
                    , 'type'        => TYPE_INTEGER
                )
                ,'status' => array(
                    'name'          => 'status'
                    , 'type'        => TYPE_INTEGER
                )
                ,'updated_at' => array(
                    'name'          => 'updated_at'
                    , 'type'        => TYPE_DATETIME
                )
                ,'author_id' => array(
                      'name'          => 'author_id'
                    , 'type'        => TYPE_INTEGER
                ))
            , 'lists'     => array()
            , 'search'    => array(
                'updated_atLE' => array(
                      'name'       => 'updated_at'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_LE
                )
                ,'posted_atGE' => array(
                      'name'       => 'posted_at'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_GE
                ),
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

        /** @return InstObservedPost[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return InstObservedPost */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return InstObservedPost */
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

        /** @return InstObservedPost */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>