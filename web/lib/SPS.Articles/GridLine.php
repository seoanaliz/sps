<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * GridLine
     *
     * @package SPS
     * @subpackage Articles
     */
    class GridLine {

        /** @var int */
        public $gridLineId;

        /** @var DateTimeWrapper */
        public $startDate;

        /** @var DateTimeWrapper */
        public $endDate;

        /** @var DateTimeWrapper */
        public $time;

        /** @var string */
        public $type;

        /** @var int */
        public $targetFeedId;

        /** @var TargetFeed */
        public $targetFeed;

        /** @var boolean */
        public $repeat;

    }
?>