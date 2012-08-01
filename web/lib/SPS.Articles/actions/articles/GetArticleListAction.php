<?php
    /**
     * Get Article List Action
     * 
     * @package SPS
     * @subpackage Articles
     * @property Article[] list
     */
    class GetArticleListAction extends BaseGetAction {

        /**
         * Constructor
         */
        public function __construct() {
            $this->options = array(
                BaseFactory::WithoutDisabled => false
                , BaseFactory::WithLists     => false
            );

            parent::$factory = new ArticleFactory();
        }
        
        
        /**
         * Set Foreign Lists
         */
        protected function setForeignLists() {
            Response::setArray( "sourceFeeds", SourceFeedUtility::GetAll() );
        }
    }
?>