<?php
    /**
     * Save Author Action
     * 
     * @package SPS
     * @subpackage App
     * @property Author originalObject
     * @property Author currentObject
     */
    class SaveAuthorAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new AuthorFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Author $originalObject 
         * @return Author
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Author $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->authorId = $originalObject->authorId;
            }

            $targetFeedIds = Request::getArray( 'targetFeedIds' );
            $targetFeedIds = !empty($targetFeedIds) ? $targetFeedIds : array();
            $object->targetFeedIds = implode(',', $targetFeedIds);
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param Author $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Author $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Author $object
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
            $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutDisabled => false ) );
            Response::setArray( 'targetFeeds', $targetFeeds );
        }
    }
?>