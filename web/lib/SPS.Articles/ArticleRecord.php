<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * ArticleRecord
     *
     * @package SPS
     * @subpackage Articles
     */
    class ArticleRecord {

        /** @var int */
        public $articleRecordId;

        /** @var string */
        public $content;

        /** @var int */
        public $likes;

        /** @var int */
        public $articleId;

        /** @var Article */
        public $article;

        /** @var int */
        public $articleQueueId;

        /** @var ArticleQueue */
        public $articleQueue;
    }
?>