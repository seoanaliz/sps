<?php
    /**
     * Save AndroidSetting Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property AndroidSetting originalObject
     * @property AndroidSetting currentObject
     */
    class SaveAndroidSettingAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new AndroidSettingFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param AndroidSetting $originalObject 
         * @return AndroidSetting
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var AndroidSetting $object
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
         * @param AndroidSetting $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param AndroidSetting $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param AndroidSetting $object
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