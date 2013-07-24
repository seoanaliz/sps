<?php
    /**
     * Save Banner Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property Banner originalObject
     * @property Banner currentObject
     */
    class SaveBannerAction extends BaseSaveAction  {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new BannerFactory();
        }

               
        /**
         * Form Object From Request
         *
         * @param Banner $originalObject 
         * @return Banner
         */
        protected function getFromRequest( $originalObject = null ) {
            /**
             * @var Banner $object
             */
            $object = parent::$factory->GetFromRequest();
            
            if ( $originalObject != null ) {
                $object->bannerId = $originalObject->bannerId;
            }
            
            return $object;
        }
        
        
        /**
         * Validate Object
         *
         * @param Banner $object
         * @return array
         */
        protected function validate( $object ) {
            $errors = parent::$factory->Validate( $object );
            
            return $errors;
        }
        
        
        /**
         * Add Object
         *
         * @param Banner $object
         * @return bool
         */
        protected function add( $object ) {
            $result = parent::$factory->Add( $object );
            
            return $result;
        }
        
        
        /**
         * Update Object
         *
         * @param Banner $object
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