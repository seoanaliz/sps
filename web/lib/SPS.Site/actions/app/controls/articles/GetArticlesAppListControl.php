<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticlesAppListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticlesAppListControl {

        /**
         * @var Article[]
         */
        private $articles = array();

        /**
         * @var ArticleRecord[]
         */
        private $articleRecords = array();

        /**
         * @var array
         */
        private $search = array();

        /**
         * @var array
         */
        private $options = array();

        private function processRequest() {

        }

        private function getObjects() {

        }

        private function setData() {

        }

        /**
         * Entry Point
         */
        public function Execute() {
            $this->processRequest();
            $this->getObjects();
            $this->setData();
        }
    }
?>