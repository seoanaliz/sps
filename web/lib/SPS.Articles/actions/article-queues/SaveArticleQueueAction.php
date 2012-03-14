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
            }
            
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
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param ArticleQueue $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param ArticleQueue $object
         * @return bool
         */
        protected function update( $object ) {
            $result = parent::$factory->Update( $object );
            
            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "targetFeeds", $targetFeeds );
        }
    }
?>