<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveArticleControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            $id             = Request::getInteger('articleId');
            $text           = Request::getString( 'text' );
            $photos         = Request::getArray( 'photos' );
            $sourceFeedId   = Request::getInteger( 'sourceFeedId' );

            $sourceFeed     = SourceFeedFactory::GetById($sourceFeedId);
            if (empty($sourceFeedId) || empty($sourceFeed)) {
                $result['message'] = 'emptySourceFeedId';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            if (empty($text) && empty($photos)) {
                $result['message'] = 'emptyArticle';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //building data
            $article = new Article();
            $article->createdAt = DateTimeWrapper::Now();
            $article->importedAt = $article->createdAt;
            $article->sourceFeedId = $sourceFeedId;
            $article->externalId = -1;
            $article->statusId = 1;

            $articleRecord = new ArticleRecord();
            $articleRecord->content = $text;
            $articleRecord->likes = 0;
            $articleRecord->photos = $photos;

            if (!empty($id)) {
                $queryResult = $this->update($id, $articleRecord);
            } else {
                $queryResult = $this->add($article, $articleRecord);
            }

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
                if ($id) {
                    $result['id'] = $id;
                }
            }

            echo ObjectHelper::ToJSON($result);
        }

        private function add($article, $articleRecord) {
            ConnectionFactory::BeginTransaction();

            $result = ArticleFactory::Add($article);

            if ($result) {
                $article->articleId = ArticleFactory::GetCurrentId();
                $articleRecord->articleId = $article->articleId;

                $result = ArticleRecordFactory::Add($articleRecord);
            }

            ConnectionFactory::CommitTransaction($result);
            return $result;
        }

        private function update($id, $articleRecord) {
            ConnectionFactory::BeginTransaction();

            $result = ArticleRecordFactory::UpdateByMask($articleRecord, array('content', 'photots'), array('articleId' => $id));

            ConnectionFactory::CommitTransaction($result);
            return $result;
        }
    }

?>