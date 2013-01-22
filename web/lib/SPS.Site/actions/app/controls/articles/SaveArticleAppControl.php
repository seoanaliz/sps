<?php
    /**
     * SaveArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveArticleAppControl extends AppBaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            $author = $this->getAuthor();
            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

            $text           = trim(Request::getString( 'text' ));
            $targetFeedId   = Session::getInteger( 'gaal_targetFeedId' );
            $targetFeedIds  = $TargetFeedAccessUtility->getAllTargetFeedIds();

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
                //echo ObjectHelper::ToJSON($result);
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
            $article->isCleaned = false;
            $article->statusId = 1;

            $articleRecord = new ArticleRecord();
            $articleRecord->content = mb_substr($text, 0, 4100);
            $articleRecord->likes = 0;
            $articleRecord->photos = $this->getPhotos();

            if (empty($articleRecord->content) && empty($articleRecord->photos)) {
                $result['message'] = 'emptyArticle';
                //echo ObjectHelper::ToJSON($result);
                return false;
            }

            $queryResult = $this->add($article, $articleRecord);

            if (!$queryResult) {
                $result['message'] = 'saveError';
            } else {
                $result['success'] = true;
            }

            echo ObjectHelper::ToJSON($result);
        }

        private function getPhotos() {
            $result = array();
            $photos = Request::getArray( 'photos' );

            if (!empty($photos)) {
                foreach($photos as $photoItem) {
                    if (!is_array($photoItem) || empty($photoItem['filename'])) continue;
                    $path = MediaUtility::GetArticlePhoto($photoItem);
                    if (URLUtility::CheckUrl($path)) {
                        $result[] = array('filename' => $photoItem['filename']);
                    }
                }
            }

            return $result;
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