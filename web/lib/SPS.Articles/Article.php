<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Article
     *
     * @package SPS
     * @subpackage Articles
     */
    class Article {

        /** @var int */
        public $articleId;

        /** @var DateTimeWrapper */
        public $importedAt;

        /** @var DateTimeWrapper */
        public $createdAt;

        /** @var DateTimeWrapper */
        public $queuedAt;

        /** @var DateTimeWrapper */
        public $sentAt;

        /** @var string */
        public $externalId;

        /** @var int */
        public $rate;

        /** @var int */
        public $sourceFeedId;

        /** @var SourceFeed */
        public $sourceFeed;

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFeed;

        /** @var int */
        public $authorId;

        /** @var Author */
        public $author;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>