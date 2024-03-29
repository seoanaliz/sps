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

        /** @var string */
        public $link;

        /** @var array */
        public $photos;

        /** @var int */
        public $rate;

        /** @var array */
        public $retweet;

        /** @var array */
        public $video;

        /** @var array */
        public $music;

        /** @var string */
        public $map;

        /** @var string */
        public $poll;

        /** @var array */
        public $text_links;

        /** @var string */
        public $doc;

        /** @var array */
        public $topfaceData;

        /** @var int */
        public $articleId;

        /** @var Article */
        public $article;

        /** @var int */
        public $articleQueueId;

        /** @var ArticleQueue */
        public $articleQueue;

        /** @var int */
        public $repostArticleRecordId;

        /** @var string */
        public $repostExternalId;

        /** @var string */
        public $createdVia;

        /** @var string */
        public $repostPublicImage;

        /** @var string */
        public $repostPublicTitle;

    }
?>