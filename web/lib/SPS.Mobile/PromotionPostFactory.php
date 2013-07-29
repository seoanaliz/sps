<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Mobile' );

    /**
     * PromotionPost Factory
     *
     * @package SPS
     * @subpackage Mobile
     */
    class PromotionPostFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = 'tst';

        /** PromotionPost instance mapping  */
        public static $mapping = array (
            'class'       => 'PromotionPost'
            , 'table'     => 'promotionPost'
            , 'view'      => 'getPromotionPost'
            , 'flags'     => array( )
            , 'cacheDeps' => array()
            , 'fields'    => array(
                'publicId' => array(
                      'name'          => 'publicId'
                    , 'type'        => TYPE_INTEGER
                )
                ,'platform' => array(
                      'name'          => 'platform'
                    , 'type'        => TYPE_STRING
                )
                ,'headerText' => array(
                    'name'          => 'headerText'
                    , 'type'        => TYPE_STRING
                )
                ,'imgUrl' => array(
                    'name'          => 'imgUrl'
                    , 'type'        => TYPE_STRING
                )
                ,'text' => array(
                    'name'          => 'text'
                    , 'type'        => TYPE_STRING
                )
                ,'actionText' => array(
                    'name'          => 'actionText'
                    , 'type'        => TYPE_STRING
                )
                ,'actionUrl' => array(
                    'name'          => 'actionUrl'
                    , 'type'        => TYPE_STRING
                )
                ,'index' => array(
                      'name'          => 'index'
                    , 'type'        => TYPE_INTEGER
                )
                ,'active' => array(
                    'name'          => 'active'
                    , 'type'        => TYPE_STRING
                )
                ,'showsCount' => array(
                    'name'          => 'showsCount'
                    , 'type'        => TYPE_INTEGER
                )
                ,'image_width'  => array(
                      'name'  =>  'image_width'
                     ,'type'  =>  TYPE_INTEGER
                )
                ,'image_height'  => array(
                      'name'  =>  'image_height'
                     ,'type'  =>  TYPE_INTEGER
                )
                ,'id'  => array(
                      'name'  =>  'id'
                     ,'type'  =>  TYPE_INTEGER
                     ,'key'   =>  true
                )
            )
            , 'lists'     => array()
            , 'search'    => array()
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

        /** @return PromotionPost[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return PromotionPost */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return PromotionPost */
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

        /** @return PromotionPost */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>