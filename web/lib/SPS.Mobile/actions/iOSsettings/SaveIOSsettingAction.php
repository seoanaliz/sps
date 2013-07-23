<?php
    /**
     * Save IOSsetting Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property IOSsetting originalObject
     * @property IOSsetting currentObject
     */
    class SaveIOSsettingAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new IOSsettingFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param IOSsetting $originalObject 
         * @return IOSsetting
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var IOSsetting $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->publicId = $originalObject->publicId;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param IOSsetting $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param IOSsetting $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param IOSsetting $object
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