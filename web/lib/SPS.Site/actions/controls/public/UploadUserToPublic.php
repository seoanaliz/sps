<?php

class UploadUserToPublic {

    /**
     *
     */
    public function Execute() {
        $publicId = Request::getInteger('publicId');
        $userId = Request::getInteger('userId');
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

        ArticleFactory::Add($article);
    }
}
