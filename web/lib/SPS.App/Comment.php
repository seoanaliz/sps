<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Comment
     *
     * @package SPS
     * @subpackage App
     */
    class Comment {

        /** @var int */
        public $commentId;

        /** @var string */
        public $text;

        /** @var DateTimeWrapper */
        public $createdAt;

        /** @var int */
        public $articleId;

        /** @var Article */
        public $article;

        /** @var int */
        public $authorId;

        /** @var Author */
        public $author;

        /** @var int */
        public $editorId;

        /** @var Editor */
        public $editor;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>