<?php
    /**
     * Get AndroidSetting List Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property AndroidSetting[] list
     */
    class GetAndroidSettingListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new AndroidSettingFactory();
            $this->connectionName = 'tst';
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}

    }
?>