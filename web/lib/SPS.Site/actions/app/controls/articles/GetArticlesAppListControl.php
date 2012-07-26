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
         * @var TargetFeed[]
         */
        private $targetFeeds = array();

        /**
         * @var Author[]
         */
        private $authors = array();

        /**
         * @var int
         */
        private $pageSize = 20;

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
            if (empty($type) || $type == 'null') {
                $type = Session::getString('gaal_type');
            }
            Session::setString('gaal_type', $type);

            //все авторские посты
            $this->search['sourceFeedId'] = -1;

            if (substr($type, 0, 1) == 'p') {
                $targetFeedId   = substr($type, 1, strlen($type) - 1);
                $targetFeedIds  = Session::getArray('targetFeedIds');
                if (empty($targetFeedIds) || !in_array($targetFeedId, $targetFeedIds)) {
                    $type = 'my';
                } else {
                    $type = 'targetFeed';
                }
            } else {
                $type = 'my';
            }

            Session::setInteger('gaal_targetFeedId', null);

            switch ($type) {
                case 'targetFeed':
                    $this->search['targetFeedId'] = $targetFeedId;
                    Session::setInteger('gaal_targetFeedId', $targetFeedId);
                    break;
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

            //load articles data
            if (!empty($this->articles)) {
                $this->articleRecords = ArticleRecordFactory::Get(
                    array('_articleId' => array_keys($this->articles))
                );
            }
            if (!empty($this->articleRecords)) {
                $this->articleRecords = BaseFactoryPrepare::Collapse($this->articleRecords, 'articleId', false);
            }

            if ($this->hasMore) {
                Session::setInteger('gaal_page', $this->search['page'] + 1);
            }

            //get articles target feeds with info and authors
            if (!empty($this->articles)) {
                $targetFeedIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('targetFeedId'));
                $authorIds = ArrayHelper::GetObjectsFieldValues($this->articles, array('authorId'));

                if (!empty($targetFeedIds)) {
                    $this->targetFeeds = TargetFeedFactory::Get(array('_targetFeedId' => array_unique($targetFeedIds)));
                }
                if (!empty($authorIds)) {
                    $this->authors = AuthorFactory::Get(
                        array('_authorId' => array_unique($authorIds)),
                        array(BaseFactory::WithoutPages => true)
                    );
                }
            }
        }

        private function setData() {
            Response::setArray( 'articles', $this->articles );
            Response::setArray( 'articleRecords', $this->articleRecords );
            Response::setInteger( 'articlesCount', $this->articlesCount );
            Response::setBoolean( 'hasMore', $this->hasMore );
            Response::setArray( 'authors', $this->authors );
            Response::setArray( 'targetFeeds', $this->targetFeeds );
            Response::setArray( 'targetInfo', SourceFeedUtility::GetInfo($this->targetFeeds, 'targetFeedId') );
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