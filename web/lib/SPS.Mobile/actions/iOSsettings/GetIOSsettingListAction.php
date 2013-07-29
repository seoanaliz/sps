<?php
    /**
     * Get IOSsetting List Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property IOSsetting[] list
     */
    class GetIOSsettingListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new IOSsettingFactory();
            $this->connectionName = 'tst';
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>