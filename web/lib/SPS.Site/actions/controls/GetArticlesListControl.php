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

            //group info
            $sourceInfo = array(
                'name' => $sourceFeed->title,
                'img' => '',
            );

            //group image
            $path = 'temp://userpic-' . $sourceFeed->externalId . '.jpg';
            $filePath = Site::GetRealPath($path);
            if (file_exists($filePath)) {

            } else {
                try {
                    $parser = new ParserVkontakte();
                    $info = $parser->get_info(ParserVkontakte::VK_URL . '/public' . $sourceFeed->externalId);
                    file_put_contents($filePath, file_get_contents($info['avatarа']));
                } catch (Exception $Ex) {}
            }

            $sourceInfo['img'] = Site::GetWebPath($path);


            Response::setArray( 'articles', $articles );
            Response::setArray( 'articleRecords', $articleRecords );
            Response::setBoolean( 'hasMore', $hasMore );
            Response::setParameter( 'sourceFeed', $sourceFeed );
            Response::setArray( 'sourceInfo', $sourceInfo );
        }
    }

?>