<?php
    /**
     * Save Category Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property Category originalObject
     * @property Category currentObject
     */
    class SaveCategoryAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new CategoryFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Category $originalObject 
         * @return Category
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Category $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->id = $originalObject->id;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param Category $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Category $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Category $object
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

        protected function delete( $object ) {
            return self::$factory->PhysicalDelete( $object );
        }
    }
?>