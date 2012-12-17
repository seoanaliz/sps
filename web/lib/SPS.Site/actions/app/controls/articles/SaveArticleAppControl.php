<?php
Package::Load('SPS.Site/base');

/**
 * SaveArticleControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */

/**
 * Добавление нового поста
 */
class SaveArticleAppControl extends BaseControl {

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
            return false;
        }


        $RoleAccessUtility = new RoleAccessUtility($this->vkId);

        // получаем список лент отправки и ролей
        $targetFeedIdsByRoles = $RoleAccessUtility->getTargetFeedIds();
        $role = null;
        $targetFeedId = $this->getTargetFeedId();

        if ($targetFeedId) {
            // хотим запостить в какую-то ленту
            $targetFeedIds = null;
            foreach ($targetFeedIdsByRoles as $role=>$targetFeedIds) {
                if (in_array($targetFeedId, $targetFeedIds)) {
                    break;
                }
            }

            if (!$targetFeedIds) {
                $result['message'] = 'emptyTargetFeedId';
                return false;
            }
        } else {
            // постим к себе
            $targetFeedId = -1;
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
        // при создании статус - на рассмотрении
        if ($role == UserFeed::ROLE_AUTHOR)  {
            $article->articleStatus = Article::STATUS_REVIEW;
        } else {
            $article->articleStatus = Article::STATUS_APPROVED;
        }


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