<?php
/**
 * SaveArticleControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class SaveArticleControl extends BaseControl
{

    private function convert_line_breaks($string, $line_break = PHP_EOL)
    {
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
    public function Execute()
    {
        $result = array(
            'success' => false
        );
        $id = Request::getInteger('articleId');
        $text = trim(Request::getString('text'));

        $link = trim(Request::getString('link'));
        $repostExternalId = trim(Request::getString('repostExternalId'));
        $photos = Request::getArray('photos');
        $targetFeedId = Request::getInteger('targetFeedId');
        $userGroupId = Request::getInteger('userGroupId');
        $sourceFeedId = Request::getInteger('sourceFeedId');
        if (!$userGroupId) {
            $userGroupId = null;
        }

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);
        if (is_null($role)) {
            return ObjectHelper::ToJSON(array('success' => false));
        }

        $authorId = $this->getAuthor()->authorId;

        $text = $this->convert_line_breaks($text);
        $text = strip_tags($text);

        //parsing link
        $linkInfo = UrlParser::Parse($link);
        if (empty($linkInfo)) {
            $link = null;
        }

        if (empty($text) && empty($photos) && empty($link) && empty($repostExternalId)) {
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
        $article->editor = $this->vkId;
        $article->authorId = $authorId;
        $article->isCleaned = false;
        $article->statusId = 1;
        $article->userGroupId = $userGroupId;
        $article->articleStatus = $role == UserFeed::ROLE_AUTHOR ? Article::STATUS_REVIEW : Article::STATUS_APPROVED;

        if ($sourceFeedId) {
            $SourceFeed = SourceFeedFactory::GetById($sourceFeedId);
            if ($SourceFeed) {
                $article->sourceFeedId = $SourceFeed->sourceFeedId;
                if ($SourceFeed->type == SourceFeedUtility::Ads) {
                    $article->articleStatus = Article::STATUS_APPROVED;
                    $article->authorId = null;
                }
            }
        }

        $articleRecord = new ArticleRecord();
        $articleRecord->content = $text ? $text : '';
        $articleRecord->likes = 0;
        $articleRecord->photos = !empty($photos) ? $photos : array();
        $articleRecord->link = $link;
        if( ! $repostExternalId ) {
            $articleRecord->repostArticleRecordId = $this->add_repost_article( $repostExternalId );
            if( $articleRecord->repostArticleRecordId ) {
                $articleRecord->repostExternalId = $repostExternalId;
            }
        }
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

        if ($result['success']) {
            $result['articleId'] = $article->articleId;
        }

        echo ObjectHelper::ToJSON($result);
    }

    private function add($article, $articleRecord)
    {
        ConnectionFactory::BeginTransaction();

        $result = ArticleFactory::Add($article, array(BaseFactory::WithReturningKeys => true));
        if ($result) {
            $articleRecord->articleId = $article->articleId;
            $result = ArticleRecordFactory::Add($articleRecord);
        }

        ConnectionFactory::CommitTransaction($result);
        return $result;
    }

    private function update($id, $articleRecord)
    {
        ConnectionFactory::BeginTransaction();

        $result = ArticleRecordFactory::UpdateByMask($articleRecord, array('content', 'photos', 'link', 'repostArticleRecordId','repostExternalId'), array('articleId' => $id));

        ConnectionFactory::CommitTransaction($result);
        return $result;
    }

    private function add_repost_article( $repostExternalId )
    {
        $articleRecord = new ArticleRecord();
        try {
            $posts =  ParserVkontakte::get_posts_by_vk_id( $repostExternalId );
            if( !isset( $posts[0])){
                throw new Exception( "failed to load post");
            }
            $post = $posts[0];
            $articleRecord->content = $post['text']? $post['text'] : '';
            $articleRecord->likes = Convert::ToInteger($post['likes_tr']);
            $articleRecord->link = Convert::ToString($post['link']);
            $articleRecord->retweet = Convert::ToArray($post['retweet']);
            $articleRecord->text_links = Convert::ToArray($post['text_links']);
            $articleRecord->video = Convert::ToArray($post['video']);
            $articleRecord->music = Convert::ToArray($post['music']);
            $articleRecord->poll = Convert::ToString($post['poll']);
            $articleRecord->map = Convert::ToString($post['map']);
            $articleRecord->doc = Convert::ToString($post['doc']);
            $articleRecord->rate = 0;

            foreach ($post['photo'] as $photo) {
                $photos[] = array(
                    'filename' => '',
                    'title' => !empty($photo['desc']) ? TextHelper::ToUTF8($photo['desc']) : '',
                    'url' => $photo['url'],
                );
            }
            $articleRecord->photos = $photos;
            $conn = ConnectionFactory::Get();
            $conn->begin();

            ArticleRecordFactory::Add( $articleRecord, array(BaseFactory::WithReturningKeys => true));
            if ($articleRecord->articleRecordId) {
                $conn->commit();
            } else {
                $conn->rollback();
            }
        } catch (Exception $e) {
            die( ObjectHelper::ToJSON(array('success'=>false, 'message'=>$e->getMessage())));
        }

        return $articleRecord->articleRecordId;
    }
}
?>
