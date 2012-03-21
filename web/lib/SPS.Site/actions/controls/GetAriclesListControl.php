<?php
    Package::Load( 'SPS.Site' );

    /**
     * GetAriclesListControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class GetAriclesListControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $pageSize       = 10;
            $sourceFeedId   = Request::getInteger( 'sourceFeedId' );

            if(empty($sourceFeedId)) {
                return;
            }

            $articles = ArticleFactory::Get(
                array('sourceFeedId' => $sourceFeedId, 'pageSize' => $pageSize + 1)
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

            Response::setArray( 'articles', $articles );
            Response::setArray( 'articleRecords', $articleRecords );
            Response::setBoolean( 'hasMore', $hasMore );
        }
    }

?>