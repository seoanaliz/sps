<?php
    /**
     * SaveArticleControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class SaveArticleControl extends BaseControl {

        private function convert_line_breaks($string, $line_break=PHP_EOL) {
            $patterns = array(
                "/(<br>|<br \/>|<br\/>|<div>)\s*/i",
                "/(\r\n|\r|\n)/",
            );
            $replacements = array(
                PHP_EOL,
                $line_break
            );
            $string = preg_replace($patterns, $replacements, $string);
            return $string;
        }


        /**
         * Entry Point
         */
        public function Execute() {
            $result = array(
                'success' => false
            );

            $id             = Request::getInteger('articleId');
            $text           = trim(Request::getString( 'text' ));
            $link           = trim(Request::getString( 'link' ));
            $photos         = Request::getArray( 'photos' );
            $targetFeedId   = Request::getInteger('targetFeedId');
            $userGroupId   = Request::getInteger('userGroupId');
            $sourceFeedId = Request::getInteger('sourceFeedId');
            if (!$userGroupId){
                $userGroupId = null;
            }

            $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
            if (is_null($role)){
                return ObjectHelper::ToJSON(array('success'=> false));
            }

            $authorId = $this->getAuthor()->authorId;

            $text = $this->convert_line_breaks($text);
            $text = strip_tags($text);

            //parsing link
            $linkInfo = UrlParser::Parse($link);
            if (empty($linkInfo)) {
                $link = null;
            }

            if (empty($text) && empty($photos) && empty($link)) {
                $result['message'] = 'emptyArticle';
                echo ObjectHelper::ToJSON($result);
                return false;
            }

            //building data
            $article = new Article();
            $article->createdAt = DateTimeWrapper::Now();
            $article->importedAt = $article->createdAt;
            $article->sourceFeedId = -1;
            $article->targetFeedId = $targetFeedId;
            $article->externalId = -1;
            $article->rate = 0;
            $article->editor = AuthUtility::GetCurrentUser('Editor')->vkId;
            $article->authorId = $authorId;
            $article->isCleaned = false;
            $article->statusId = 1;
            $article->userGroupId = $userGroupId;
            $article->articleStatus = $role == UserFeed::ROLE_AUTHOR ? Article::STATUS_REVIEW : Article::STATUS_APPROVED;

            if ($sourceFeedId){
                $SourceFeed = SourceFeedFactory::GetById($sourceFeedId);
                if ($SourceFeed){
                    $article->sourceFeedId = $SourceFeed->sourceFeedId;
                    if ($SourceFeed->type == SourceFeedUtility::Ads){
                        $article->articleStatus = Article::STATUS_APPROVED;
                    }
                }
            }

            $articleRecord = new ArticleRecord();
            $articleRecord->content = $text;
            $articleRecord->likes = 0;
            $articleRecord->photos = !empty($photos) ? $photos : array();
            $articleRecord->link = $link;

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

            $result = ArticleRecordFactory::UpdateByMask($articleRecord, array('content', 'photos', 'link'), array('articleId' => $id));

            ConnectionFactory::CommitTransaction($result);
            return $result;
        }
    }

?>