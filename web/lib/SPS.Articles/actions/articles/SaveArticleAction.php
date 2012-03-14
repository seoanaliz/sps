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
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new ArticleFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Article $originalObject 
         * @return Article
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Article $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->articleId = $originalObject->articleId;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param Article $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Article $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Article $object
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
            $sourceFeeds = SourceFeedFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "sourceFeeds", $sourceFeeds );
        }
    }
?>