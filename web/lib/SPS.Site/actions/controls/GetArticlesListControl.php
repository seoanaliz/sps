<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetArticlesListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetArticlesListControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $sourceFeedIds = Request::getArray('sourceFeedIds');
            $sourceFeedIds = !empty($sourceFeedIds) ? $sourceFeedIds : array();

            SettingsUtility::SetSources($sourceFeedIds);
            if(empty($sourceFeedIds)) {
                return;
            }

            $page = Session::getInteger( 'page' );
            $page = ($page < 0) ? 0 : $page;
            $pageSize = 20;
            $clean = Request::getBoolean( 'clean' );
            if ($clean) {
                $page = 0;
            }

            $search = array(
                '_sourceFeedId' => $sourceFeedIds,
                'pageSize' => $pageSize + 1,
                'page' => $page,
            );
            $options = array();

            $from = Request::getInteger( 'from' );
            $to = Request::getInteger( 'to' );
            $sortType = Request::getString( 'sortType' );

            if ($from !== null) {
                $search['rateGE'] = $from;
            }
            if ($to !== null && $to < 100) {
                $search['rateLE'] = $to;
            }
            if ($sortType == 'old') {
                $options[BaseFactory::OrderBy] = ' "createdAt" ASC, "articleId" ASC ';
            } else if ($sortType == 'best') {
                $options[BaseFactory::OrderBy] = ' "rate" DESC, "createdAt" DESC, "articleId" DESC ';
            }

            $articles = ArticleFactory::Get( $search, $options );
            $articlesCount = ArticleFactory::Count( $search, array(BaseFactory::WithoutPages => true) );

            if (empty($articles)) {
                return;
            }

            $hasMore = (count($articles) > $pageSize);
            $articles = array_slice($articles, 0, $pageSize, true);

            //load arciles data
            $articleRecords = ArticleRecordFactory::Get(
                array('_articleId' => array_keys($articles))
            );
            if (!empty($articleRecords)) {
                $articleRecords = BaseFactoryPrepare::Collapse($articleRecords, 'articleId', false);
            }

            if ($hasMore) {
                Session::setInteger('page', $page+1);
            }

            $sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $sourceFeedIds));

            Response::setArray( 'articles', $articles );
            Response::setArray( 'articleRecords', $articleRecords );
            Response::setArray( 'sourceFeeds', $sourceFeeds );
            Response::setArray( 'sourceInfo', SourceFeedUtility::GetInfo($sourceFeeds) );
            Response::setInteger( 'articlesCount', $articlesCount );
            Response::setBoolean( 'hasMore', $hasMore );
        }
    }

?>