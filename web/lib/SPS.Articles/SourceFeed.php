<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * SourceFeed
     *
     * @package SPS
     * @subpackage Articles
     */
    class SourceFeed {

        /** @var int */
        public $sourceFeedId;

        /** @var string */
        public $title;

        /** @var string */
        public $externalId;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;
    }
?>