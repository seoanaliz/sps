<?php
    /**
     * Save TargetFeed Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property TargetFeed originalObject
     * @property TargetFeed currentObject
     */
    class SaveTargetFeedAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new TargetFeedFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param TargetFeed $originalObject 
         * @return TargetFeed
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var TargetFeed $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->targetFeedId = $originalObject->targetFeedId;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param TargetFeed $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param TargetFeed $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param TargetFeed $object
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