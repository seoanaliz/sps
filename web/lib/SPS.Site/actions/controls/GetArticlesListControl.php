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
            $pageSize       = 10;
            $sourceFeedId   = Request::getInteger( 'sourceFeedId' );
            $sourceFeed     = SourceFeedFactory::GetById($sourceFeedId);
            if(empty($sourceFeedId) || empty($sourceFeed)) {
                return;
            }

            $page           = Session::getInteger( 'page' );
            $page = ($page < 0) ? 0 : $page;
            $clean = Request::getBoolean( 'clean' );
            if ($clean) {
                $page = 0;
            }

            $articles = ArticleFactory::Get(
                array('sourceFeedId' => $sourceFeedId, 'pageSize' => $pageSize + 1, 'page' => $page)
            );

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

            Response::setArray( 'articles', $articles );
            Response::setArray( 'articleRecords', $articleRecords );
            Response::setBoolean( 'hasMore', $hasMore );
            Response::setParameter( 'sourceFeed', $sourceFeed );
        }
    }

?>