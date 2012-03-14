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

        /** @var int */
        public $sourceFeedId;

        /** @var SourceFeed */
        public $sourceFee;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>