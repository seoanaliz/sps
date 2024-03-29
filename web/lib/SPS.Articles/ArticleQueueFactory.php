<?php
    /**
     * WTF MFD EG 1.6
     * Copyright (c) The 1ADW. All rights reserved.
     */
          
    Package::Load( 'SPS.Articles' );

    /**
     * ArticleQueue Factory
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleQueueFactory implements IFactory {

        /** Default Connection Name */
        const DefaultConnection = null;

        /** ArticleQueue instance mapping  */
        public static $mapping = array (
            'class'       => 'ArticleQueue'
            , 'table'     => 'articleQueues'
            , 'view'      => 'getArticleQueues'
            , 'flags'     => array( 'CanPages' => 'CanPages', 'CanCache' => false )
            , 'cacheDeps' => array( 'articles', 'targetFeeds' )
            , 'fields'    => array(
                'articleQueueId' => array(
                    'name'          => 'articleQueueId'
                    , 'type'        => TYPE_INTEGER
                    , 'key'         => true
                )
                ,'startDate' => array(
                    'name'          => 'startDate'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'endDate' => array(
                    'name'          => 'endDate'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'createdAt' => array(
                    'name'          => 'createdAt'
                    , 'type'        => TYPE_DATETIME
                    , 'nullable'    => 'No'
                )
                ,'sentAt' => array(
                    'name'          => 'sentAt'
                    , 'type'        => TYPE_DATETIME
                )
                ,'type' => array(
                    'name'          => 'type'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 10
                    , 'nullable'    => 'CheckEmpty'
                )
                ,'author' => array(
                    'name'          => 'author'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                )
                ,'externalId' => array(
                    'name'          => 'externalId'
                    , 'type'        => TYPE_STRING
                    , 'max'         => 100
                )
                ,'externalLikes' => array(
                    'name'          => 'externalLikes'
                    , 'type'        => TYPE_INTEGER
                )
                ,'externalRetweets' => array(
                    'name'          => 'externalRetweets'
                    , 'type'        => TYPE_INTEGER
                )
                ,'articleId' => array(
                    'name'          => 'articleId'
                    , 'type'        => TYPE_INTEGER
                    , 'foreignKey'  => 'Article'
                )
                ,'targetFeedId' => array(
                    'name'          => 'targetFeedId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'TargetFeed'
                )
                ,'statusId' => array(
                    'name'          => 'statusId'
                    , 'type'        => TYPE_INTEGER
                    , 'nullable'    => 'CheckEmpty'
                    , 'foreignKey'  => 'Status'
                )
                ,'isDeleted' => array(
                    'name'          => 'isDeleted'
                    , 'type'        => TYPE_BOOLEAN
                    )
                ,'deleteAt' => array(
                    'name'          => 'deleteAt'
                    , 'type'        => TYPE_DATETIME
                )
                ,'collectLikes' => array(
                    'name'          => 'collectLikes'
                , 'type'        => TYPE_BOOLEAN
                )
                ,'protectTo'    => array(
                      'name'        => 'protectTo'
                    , 'type'        => TYPE_DATETIME
                )
                ,'addedFrom' => array(
                    'name'          => 'addedFrom'
                    , 'type'        => TYPE_STRING
                )
            )
            , 'lists'     => array()
                , 'search'    => array(
                'startDateAsDate' => array(
                    'name'         => 'startDate'
                    , 'type'       => TYPE_DATE
                )
                ,'page' => array(
                    'name'         => 'page'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 0
                )
                ,'pageSize' => array(
                    'name'         => 'pageSize'
                    , 'type'       => TYPE_INTEGER
                    , 'default'    => 25
                )
                ,'sentAtFrom' => array(
                    'name'         => 'sentAt',
                    'type'       => TYPE_DATETIME,
                    'searchType' => SEARCHTYPE_GE
                ),
                'sentAtTo' => array(
                    'name'         => 'sentAt',
                    'type'       => TYPE_DATETIME,
                    'searchType' => SEARCHTYPE_LE
                )
                ,'externalIdNot' => array(
                    'name'         => 'externalId',
                    'type'          => TYPE_STRING,
                    'searchType' => SEARCHTYPE_NOT_EQUALS
                )
                ,'externalIdExist' => array(
                    'name'         => 'externalId',
                    'type'          => TYPE_BOOLEAN,
                    'searchType' => SEARCHTYPE_NOT_NULL
                )
                ,'emptyExternalLikes'=> array(
                    'name'         => 'externalLikes',
                    'type'          => TYPE_BOOLEAN,
                    'searchType' => SEARCHTYPE_NULL
                )
                , 'startDateFrom' => array(
                    'name'         => 'startDate'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_GE
                )
                , 'startDateTo' => array(
                    'name'         => 'startDate'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_LE
                )
                , 'protectToGE' => array(
                    'name'         => 'protectTo'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_GE
                )
                , 'protectToLE' => array(
                    'name'         => 'protectTo'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_LE
                )
                , 'createdAtNE' => array(
                      'name'       => 'createdAt'
                    , 'type'       => TYPE_DATETIME
                    , 'searchType' => SEARCHTYPE_NOT_EQUALS
                )
                , 'articleQueueIdNE' => array(
                      'name'       => 'articleQueueId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_NOT_EQUALS
                )
                , 'statusIdNE' => array(
                      'name'       => 'statusId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_NOT_EQUALS
                )
                , 'statudIdIn' => array(
                    'name'         => 'statudId'
                    , 'type'       => TYPE_INTEGER
                    , 'searchType' => SEARCHTYPE_ARRAY
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

        /** @return ArticleQueue[] */
        public static function Get( $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::Get( $searchArray, self::$mapping, $options, $connectionName );
        }

        /** @return ArticleQueue */
        public static function GetById( $id, $searchArray = null, $options = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetById( $id, $searchArray, self::$mapping, $options, $connectionName );
        }
        
        /** @return ArticleQueue */
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

        /** @return ArticleQueue */
        public static function GetFromRequest( $prefix = null, $connectionName = self::DefaultConnection ) {
            return BaseFactory::GetFromRequest( $prefix, self::$mapping, null, $connectionName );
        }
        
    }
?>