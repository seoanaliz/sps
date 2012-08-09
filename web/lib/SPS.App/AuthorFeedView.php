<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * AuthorFeedView
     *
     * @package SPS
     * @subpackage App
     */
    class AuthorFeedView {

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFeed;

        /** @var int */
        public $authorId;

        /** @var Author */
        public $author;

        /** @var DateTimeWrapper */
        public $lastViewDate;
    }
?>