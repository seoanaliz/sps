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
         * @var int
         */
        private $pageSize = 20;

        /**
         * @var Article[]
         */
        private $articles = array();

        /**
         * @var ArticleRecord[]
         */
        private $articleRecords = array();

        /**
         * @var int
         */
        private $articlesCount = 0;

        /**
         * @var bool
         */
        private $hasMore = false;

        /**
         * @var array
         */
        private $search = array();

        /**
         * @var array
         */
        private $options = array();

        private function processRequest() {
            $author = Session::getObject('Author');
            $page = Session::getInteger('gaal_page');
            $page = ($page < 0) ? 0 : $page;
            if (Request::getBoolean('clear')) {
                $page = 0;
            }

            $this->search = array(
                'pageSize' => $this->pageSize + 1,
                'page' => $page,
            );

            $type = Request::getString('type');
            if (empty($type)) {
                $type = Session::getString('gaal_type');
            }
            Session::setString('gaal_type', $type);

            $this->search['sourceFeedId'] = -1;

            switch ($type) {
                case 'my':
                default:
                    $this->search['authorId'] = $author->authorId;
                    break;
            }
        }

        private function getObjects() {
            $this->articles = ArticleFactory::Get($this->search, $this->options);
            $this->articlesCount = ArticleFactory::Count($this->search, array(BaseFactory::WithoutPages => true));

            $this->hasMore = (count($this->articles) > $this->pageSize);
            $this->articles = array_slice($this->articles, 0, $this->pageSize, true);

            //load arciles data
            $this->articleRecords = ArticleRecordFactory::Get(
                array('_articleId' => array_keys($this->articles))
            );
            if (!empty($this->articleRecords)) {
                $this->articleRecords = BaseFactoryPrepare::Collapse($this->articleRecords, 'articleId', false);
            }

            if ($this->hasMore) {
                Session::setInteger('gaal_page', $this->search['page'] + 1);
            }
        }

        private function setData() {
            Response::setArray( 'articles', $this->articles );
            Response::setArray( 'articleRecords', $this->articleRecords );
            Response::setInteger( 'articlesCount', $this->articlesCount );
            Response::setBoolean( 'hasMore', $this->hasMore );
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