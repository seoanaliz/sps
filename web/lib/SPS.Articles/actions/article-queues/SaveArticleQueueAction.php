<?php
    /**
     * Save ArticleQueue Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property ArticleQueue originalObject
     * @property ArticleQueue currentObject
     */
    class SaveArticleQueueAction extends BaseSaveAction  {

        /**
         * @var ArticleRecord
         */
        private $articleRecord;
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new ArticleQueueFactory();
        }
               
        /**
         * Form Object From Request
         *
         * @param ArticleQueue $originalObject 
         * @return ArticleQueue
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var ArticleQueue $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->articleQueueId = $originalObject->articleQueueId;
                $object->createdAt      = $originalObject->createdAt;
            } else {
                $object->createdAt      = DateTimeWrapper::Now();
            }

            $this->articleRecord = ArticleRecordFactory::GetFromRequest( "articleRecord" );
            $this->articleRecord->articleId         = null; //NB
            $this->articleRecord->articleRecordId   = null; //NB

            //set original articleRecordId if exists
            if ( $originalObject != null ) {
                $originalArticleRecord = ArticleRecordFactory::GetOne(
                    array('articleQueueId' => $this->originalObject->articleQueueId)
                    , array(BaseFactory::WithColumns => '"articleRecordId"')
                );

                if (!empty($originalArticleRecord) && !empty($originalArticleRecord->articleRecordId)) {
                    $this->articleRecord->articleRecordId = $originalArticleRecord->articleRecordId;
                }
            }

            //force articleId
            $articleId = Request::getInteger( 'articleId' );
            if ($articleId) {
                $article = ArticleFactory::GetById($articleId);
                if ($article) {
                    $object->articleId = $articleId;

                    //force article records fields
                    $forceArticleRecord = ArticleRecordFactory::GetOne(
                        array('articleId' => $articleId)
                    );

                    if (!empty($forceArticleRecord)) {
                        $this->articleRecord->content   = $forceArticleRecord->content;
                        $this->articleRecord->likes     = $forceArticleRecord->likes;
                    }
                }
            }

            Response::setParameter( "articleRecord", $this->articleRecord );

            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param ArticleQueue $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            $articleRecordErrors = ArticleRecordFactory::Validate( $this->articleRecord );

            if( !empty( $articleRecordErrors['fields'] ) ) {
                foreach( $articleRecordErrors['fields'] as $key => $value ) {
                    $errors['fields'][$key] = $value;
                }
            }
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param ArticleQueue $object
         * @return bool
         */
        protected function add( $object ) {
            $conn = ConnectionFactory::Get();
            $conn->begin();

            $result = parent::$factory->Add( $object );

            $this->articleRecord->articleQueueId = parent::$factory->GetCurrentId();

            if ( $result ) {
                if( empty( $this->articleRecord->articleRecordId ) ) {
                    $result = ArticleRecordFactory::Add( $this->articleRecord );
                } else {
                    $result = ArticleRecordFactory::Update( $this->articleRecord );
                }
            }

            if ( $result ) {
                $conn->commit();
            } else {
                $conn->rollback();
            }

            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param ArticleQueue $object
         * @return bool
         */
        protected function update( $object ) {
            $conn = ConnectionFactory::Get();
            $conn->begin();

            $result = parent::$factory->Update( $object );

            $this->articleRecord->articleQueueId = $object->articleQueueId;

            if ( $result ) {
                if( empty( $this->articleRecord->articleRecordId ) ) {
                    $result = ArticleRecordFactory::Add( $this->articleRecord );
                } else {
                    $result = ArticleRecordFactory::Update( $this->articleRecord );
                }
            }

            if ( $result ) {
                $conn->commit();
            } else {
                $conn->rollback();
            }

            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "targetFeeds", $targetFeeds );

            /*
            * Creating new ArticleRecord object or select existing
            */
            if( !empty( $this->originalObject ) ) {
                $this->articleRecord = ArticleRecordFactory::GetOne( array('articleQueueId' => $this->originalObject->articleQueueId) );
            }

            if( empty( $this->articleRecord ) ) {
                $this->articleRecord = new ArticleRecord();
            }

            Response::setParameter( "articleRecord", $this->articleRecord );
        }
    }
?>