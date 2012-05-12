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

            $pageSize      = 20;
            if(empty($sourceFeedIds)) {
                return;
            }

            Session::setArray('currentSourceFeedIds', $sourceFeedIds);

            $page           = Session::getInteger( 'page' );
            $page = ($page < 0) ? 0 : $page;
            $clean = Request::getBoolean( 'clean' );
            if ($clean) {
                $page = 0;
            }

            $articles = ArticleFactory::Get(
                array('_sourceFeedId' => $sourceFeedIds, 'pageSize' => $pageSize + 1, 'page' => $page)
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

            $sourceFeeds = SourceFeedFactory::Get(array('_sourceFeedId' => $sourceFeedIds));
            $this->setInfo($sourceFeeds);


            Response::setArray( 'articles', $articles );
            Response::setArray( 'articleRecords', $articleRecords );
            Response::setBoolean( 'hasMore', $hasMore );
        }

        private function setInfo($sourceFeeds) {
            $sourceInfo = array();

            foreach ($sourceFeeds as $sourceFeed) {
                $sourceInfo[$sourceFeed->sourceFeedId] = array(
                    'name' => $sourceFeed->title,
                    'img' => ''
                );

                //group image
                $path = 'temp://userpic-' . $sourceFeed->externalId . '.jpg';
                $filePath = Site::GetRealPath($path);
                if (!file_exists($filePath)) {
                    $avatarPath = Site::GetWebPath('images://fe/no-avatar.png');

                    try {
                        $parser = new ParserVkontakte();
                        $info = $parser->get_info(ParserVkontakte::VK_URL . '/public' . $sourceFeed->externalId);

                        if (!empty($info['avatarа'])) {
                            $avatarPath = $info['avatarа'];
                        }
                    } catch (Exception $Ex) {}

                    file_put_contents($filePath, file_get_contents($avatarPath));
                }

                $sourceInfo[$sourceFeed->sourceFeedId]['img'] = Site::GetWebPath($path);
            }

            Response::setArray( 'sourceInfo', $sourceInfo );
        }
    }

?>