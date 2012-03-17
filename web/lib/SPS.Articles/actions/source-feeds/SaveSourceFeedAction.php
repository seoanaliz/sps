<?php
    /**
     * Save SourceFeed Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property SourceFeed originalObject
     * @property SourceFeed currentObject
     */
    class SaveSourceFeedAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new SourceFeedFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param SourceFeed $originalObject 
         * @return SourceFeed
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var SourceFeed $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->sourceFeedId = $originalObject->sourceFeedId;
                $object->processed = $originalObject->processed;
            } else {
                $object->processed = null;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param SourceFeed $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param SourceFeed $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param SourceFeed $object
         * @return bool
         */
        protected function update( $object ) {
            $result = parent::$factory->Update( $object );
            
            return $result;
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>