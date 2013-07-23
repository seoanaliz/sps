<?php
    /**
     * Get Banner List Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property Banner[] list
     */
    class GetBannerListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new BannerFactory();
            $this->connectionName = 'tst';
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>