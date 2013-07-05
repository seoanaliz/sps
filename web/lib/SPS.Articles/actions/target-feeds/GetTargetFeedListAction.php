<?php
    /**
     * Get TargetFeed List Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property TargetFeed[] list
     */
    class GetTargetFeedListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => true
            );

            parent::$factory = new TargetFeedFactory();
        }

        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            $publishers = PublisherFactory::Get( null, array( BaseFactory::WithoutPages => true ) );
            Response::setArray( "publishers", $publishers );
        }

        protected function getSearch() {
            $search = parent::getSearch();

            if (isset($search['isOur']) && $search['isOur'] == 'on') {
                $search['isOur']  = true;
            }
            //настройки по умолчанию
            if (!isset($search)) {
                $search = array('isOur'  => true);
            }
            return $search;
        }
    }
?>