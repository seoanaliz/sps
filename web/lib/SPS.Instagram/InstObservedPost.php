<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * InstObservedPost
     *
     * @package SPS
     * @subpackage Instagram
     */
    class InstObservedPost {

        /** @var string */
        public $id;

        /** @var DateTimeWrapper */
        public $posted_at;

        /** @var string */
        public $reference_id;

        /** @var int */
        public $likes;

        /** @var int */
        public $comments;

        /** @var int */
        public $ref_start_subs;

        /** @var int */
        public $ref_end_subs;

        /** @var int */
        public $status;

        /** @var DateTimeWrapper */
        public $updated_at;

        /** @var int */
        public $author_id;
    }
?>