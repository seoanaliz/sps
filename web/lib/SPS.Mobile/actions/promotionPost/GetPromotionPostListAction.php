<?php
    /**
     * Get PromotionPost List Action
     * 
     * @package SPS
     * @subpackage Mobile
     * @property PromotionPost[] list
     */
    class GetPromotionPostListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new PromotionPostFactory();
            $this->connectionName = 'tst';
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>