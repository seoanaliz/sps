<?php
    /**
     * Get Category List Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property Category[] list
     */
    class GetCategoryListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new CategoryFactory();
            $this->connectionName = 'tst';
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>