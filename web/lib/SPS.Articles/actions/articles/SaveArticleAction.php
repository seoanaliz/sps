<?php
    /**
     * Save Article Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property Article originalObject
     * @property Article currentObject
     */
    class SaveArticleAction extends BaseSaveAction  {

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

            parent::$factory = new ArticleFactory();
        }

        protected function beforeSave() {
            $this->photosToResponse();
        }

        protected function photosToResponse() {
            $photos = array();
            if (!empty($this->articleRecord->photos)) {
                foreach($this->articleRecord->photos as $photoItem) {
                    $photo = $photoItem;
                    $photo['path'] = MediaUtility::GetArticlePhoto($photoItem, 'small');
                    $photos[] = $photo;
                }
            }
            Response::setString( 'filesJSON', ObjectHelper::ToJSON($photos) );
        }
               
        /**
         * Form Object From Request
         *
         * @param Article $originalObject 
         * @return Article
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Article $Article
             */
            $Article = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $Article->articleId = $originalObject->articleId;
            }

            $objectData = Request::getArray('article');
            if (empty($Article->sourceFeedId)) {
                $Article->sourceFeedId = !empty($objectData['sourceFeedId']) ? $objectData['sourceFeedId'] : SourceFeedUtility::FakeSourceAuthors;
            }

            $this->articleRecord = ArticleRecordFactory::GetFromRequest( "articleRecord" );
            $this->articleRecord->articleQueueId    = null; //NB
            $this->articleRecord->articleRecordId   = null; //NB

            //set original articleRecordId if exists
            if ( $originalObject != null ) {
                $originalArticleRecord = ArticleRecordFactory::GetOne(
                    array('articleId' => $this->originalObject->articleId)
                );

                if (!empty($originalArticleRecord) && !empty($originalArticleRecord->articleRecordId)) {
                    $this->articleRecord->articleRecordId = $originalArticleRecord->articleRecordId;
                }

                if (!empty($originalArticleRecord)) {
                    $this->articleRecord->topfaceData = $originalArticleRecord->topfaceData;
                }
                $Article->articleStatus = $originalObject->articleStatus;
                $Article->userGroupId = $originalObject->userGroupId;
            }
            Response::setParameter( "articleRecord", $this->articleRecord );

            //get photos from request
            $photos = Request::getArray( 'files' );
            $photos = !empty($photos) ? $photos : array();
            $this->articleRecord->photos = $photos;
            $this->photosToResponse();

            //fix arrays
            $arrays = array('retweet', 'video', 'music', 'text_links');
            $data   = Request::getArray( "articleRecord" );
            foreach ($arrays as $arrayName) {
                $value = !empty($data[$arrayName]) ? $data[$arrayName] : '[]';
                $value = ObjectHelper::FromJSON($value);
                $this->articleRecord->$arrayName = $value;
            }

            return $Article;
        }
        
        
        /**
         * Validate Object
         *
         * @param Article $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );

            $articleRecordErrors = ArticleRecordFactory::Validate( $this->articleRecord );

            if( !empty( $articleRecordErrors['fields'] ) && $this->action != BaseSaveAction::DeleteAction ) {
                foreach( $articleRecordErrors['fields'] as $key => $value ) {
                    $errors['fields'][$key] = $value;
                }
            }

            if ($object->sourceFeedId == SourceFeedUtility::FakeSourceAuthors) {
                if (empty($object->authorId)) {
                    $errors['fields']['authorId']['null'] = 'null';
                }
                if (empty($object->targetFeedId)) {
                    $errors['fields']['targetFeedId']['null'] = 'null';
                }
            }
            
            return $errors;
        }
        
        /**
         * Add Object
         *
         * @param Article $object
         * @return bool
         */
        protected function add( $object ) {
            $conn = ConnectionFactory::Get();
            $conn->begin();

            $result = parent::$factory->Add( $object );

            $this->articleRecord->articleId = parent::$factory->GetCurrentId();

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
         * @param Article $object
         * @return bool
         */
        protected function update( $object ) {
            $conn = ConnectionFactory::Get();
            $conn->begin();

            $result = parent::$factory->Update( $object );

            $this->articleRecord->articleId = $object->articleId;

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
            Response::setArray( "sourceFeeds", SourceFeedUtility::GetAll() );

            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "targetFeeds", $targetFeeds );

            /*
            * Creating new ArticleRecord object or select existing
            */
            if( !empty( $this->originalObject ) ) {
                $this->articleRecord = ArticleRecordFactory::GetOne( array('articleId' => $this->originalObject->articleId) );
            }

            if( empty( $this->articleRecord ) ) {
                $this->articleRecord = new ArticleRecord();
            }

            Response::setParameter( "articleRecord", $this->articleRecord );
        }
    }
?>