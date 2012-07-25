<?php
    /**
     * Get Author List Action
     * 
     * @package SPS
     * @subpackage App
     * @property Author[] list
     */
    class GetAuthorListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new AuthorFactory();
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {}
    }
?>