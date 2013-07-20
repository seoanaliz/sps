<?php
    /**
     * Save PromotionPost Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property PromotionPost originalObject
     * @property PromotionPost currentObject
     */
    class SavePromotionPostAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new PromotionPostFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param PromotionPost $originalObject 
         * @return PromotionPost
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var PromotionPost $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->index = $originalObject->index;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param PromotionPost $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param PromotionPost $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param PromotionPost $object
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