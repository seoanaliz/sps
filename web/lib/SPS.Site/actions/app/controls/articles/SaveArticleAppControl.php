<?php
/**
 * SaveArticleControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */

/**
 * Добавление нового поста
 */
class SaveArticleAppControl extends AppBaseControl {

    /**
     * Возвращает идентификатор запрошеной ленты
     */
    protected function getTargetFeedId(){
        $mode = Request::getString('publicId');
        if (substr($mode, 0, 1) == 'p') {
            return (int)substr($mode, 1);
        }
        return null;
    }

    /**
     * Entry Point
     */
    public function Execute()
    {
        $result = array(
            'success' => false
        );

        $author = $this->getAuthor();
        if (!$author) {
            $result['message'] = 'authentification failed';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        $userGroupId = Request::getInteger('userGroupId');
        if (!$userGroupId || !is_numeric($userGroupId)) {
            $userGroupId = null;
        }
        // TODO сделать проверку, что пользователь может добавлять статью для это группы

        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);

        $targetFeedId = $this->getTargetFeedId();

        if ($targetFeedId) {
            $role = $TargetFeedAccessUtility->getRoleForTargetFeed($targetFeedId);

            if (is_null($role)) {
                $result['message'] = 'emptyTargetFeedId';
                echo ObjectHelper::ToJSON($result);
                return false;
            }
        } else {
            $result['message'] = 'emptyTargetFeed';
            echo ObjectHelper::ToJSON($result);
            return false;
        }

        $text = trim(Request::getString('text'));

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
        $article->isSuggested = false;
        // при создании статус - на рассмотрении
        if ($role == UserFeed::ROLE_AUTHOR)  {
            $article->articleStatus = Article::STATUS_REVIEW;
        } else {
            $article->articleStatus = Article::STATUS_APPROVED;
        }
        $article->userGroupId = $userGroupId;

        $articleRecord = new ArticleRecord();
        $articleRecord->content = mb_substr($text, 0, 4100);
        $articleRecord->likes = 0;
        $articleRecord->photos = $this->getPhotos();

        if (empty($articleRecord->content) && empty($articleRecord->photos)) {
            $result['message'] = 'emptyArticle';
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

    private function getPhotos()
    {
        $result = array();
        $photos = Request::getArray('photos');

        if (!empty($photos)) {
            foreach ($photos as $photoItem) {
                if (!is_array($photoItem) || empty($photoItem['filename'])) continue;
                $path = MediaUtility::GetFilePath('Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                if (URLUtility::CheckUrl($path)) {
                    $result[] = array('filename' => $photoItem['filename']);
                }
            }
        }

        return $result;
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
}

?>