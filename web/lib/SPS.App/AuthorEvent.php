<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * AuthorEvent
     *
     * @package SPS
     * @subpackage App
     */
    class AuthorEvent {

        /** @var int */
        public $articleId;

        /** @var Article */
        public $article;

        /** @var int */
        public $authorId;

        /** @var Author */
        public $author;

        /** @var array */
        public $commentIds;

        /** @var bool */
        public $isQueued;

        /** @var bool */
        public $isSent;
    }
?>