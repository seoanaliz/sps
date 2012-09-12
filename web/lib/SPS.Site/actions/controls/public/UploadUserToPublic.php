<?php

class UploadUserToPublic {

    /**
     *
     */
    public function Execute() {
        $publicId = Request::getInteger('publicId');
        $userId = Request::getInteger('userId');
        $socialId = Request::getInteger( 'socialId' );
        $photo = Request::getString('photo');
        $firstName = Request::getString('firstName');
        $age = Request::getInteger('age');
        $text = Request::getString('text');

        $article = new Article();

        $article->importedAt = DateTimeWrapper::Now();
        $article->externalId = -1;
        $article->rate = 0;
        $article->sourceFeedId = SourceFeedUtility::FakeSourceTopface;
        $article->statusId = 1;

        $targetFeed = TargetFeedFactory::GetOne(array('externalId' => $publicId));
        if (!empty($targetFeed)) {
            $article->targetFeedId = $targetFeed->targetFeedId;
        }

        ConnectionFactory::BeginTransaction();

        $result = ArticleFactory::Add($article, array(BaseFactory::WithReturningKeys => true));

        if ($result) {
            $articleRecord = new ArticleRecord();
            $articleRecord->articleId = $article->articleId;
            $articleRecord->content = !empty($text) ? $text : '';

            $articleRecord->photos = $this->savePostPhotos($photo);
            $articleRecord->link = 'http://topface.com/vklike/' . $userId . '/';
            $articleRecord->topfaceData = array(
                'userId' => $userId,
                'socialId' => $socialId,
                'firstName' => $firstName,
                'age' => $age,
            );

            $result = ArticleRecordFactory::Add($articleRecord);
            if (!$result) {
                echo ObjectHelper::ToJSON(ArticleRecordFactory::Validate($articleRecord));
            }
        } else {
            echo ObjectHelper::ToJSON(ArticleFactory::Validate($article));
        }

        ConnectionFactory::CommitTransaction($result);
    }

    private function savePostPhotos($photo) {
        $result = array();

        if (empty($photo)) return $result;

        //moving photo to local temp
        $tmpName = Site::GetRealPath('temp://') . md5($photo) . '.jpg';
        $content = file_get_contents($photo);

        if (!$content) {
            throw new Exception('failed to get content of photo ' . $photo);
        }

        file_put_contents($tmpName, $content);
        $file = array(
            'tmp_name'  => $tmpName,
            'name'      => $tmpName,
        );
        $fileUploadResult = MediaUtility::SaveTempFile( $file, 'Article', 'photos' );

        if( !empty( $fileUploadResult['filename'] ) ) {
            MediaUtility::MoveObjectFilesFromTemp( 'Article', 'photos', array($fileUploadResult['filename']) );
            unlink($tmpName);

            $result[] = array(
                'filename' => $fileUploadResult['filename'],
                'title' => !empty($photo) ? TextHelper::ToUTF8($photo) : ''
            );
        }

        return $result;
    }
}
