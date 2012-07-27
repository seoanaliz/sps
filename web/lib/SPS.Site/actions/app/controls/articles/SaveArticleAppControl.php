<?php
    Package::Load( 'SPS.Site' );

    /**
     * SaveArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveArticleAppControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            $author = Session::getObject('Author');

            $text           = trim(Request::getString( 'text' ));
            $photos         = Request::getArray( 'photos' );
            $targetFeedId   = Session::getInteger( 'gaal_targetFeedId' );
            $targetFeedIds  = Session::getArray('targetFeedIds');

            if (!in_array($targetFeedId, $targetFeedIds)) {
                $targetFeedId = null;
            }

            //берем первый
            if (empty($targetFeedId) && empty($targetFeedIds[-1])) {
                $targetFeedId = current($targetFeedIds);
                reset($targetFeedIds);
            }

            if (empty($targetFeedIds) || !in_array($targetFeedId, $targetFeedIds)) {
                $result['message'] = 'emptyTargetFeedId';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            if (empty($text)) {
                $result['message'] = 'emptyArticle';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            $article = new Article();
            $article->createdAt = DateTimeWrapper::Now();
            $article->importedAt = $article->createdAt;
            $article->sourceFeedId = -1;
            $article->externalId = -1;
            $article->rate = 0;
            $article->targetFeedId = $targetFeedId;
            $article->authorId = $author->authorId;
            $article->statusId = 1;

            $articleRecord = new ArticleRecord();
            $articleRecord->content = $text;
            $articleRecord->likes = 0;
            $articleRecord->photos = array();

            $queryResult = $this->add($article, $articleRecord);

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
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
    }
?>