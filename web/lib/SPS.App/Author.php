<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Author
     *
     * @package SPS
     * @subpackage App
     */
    class Author {

        /** @var int */
        public $authorId;

        /** @var int */
        public $vkId;

        /** @var string */
        public $firstName;

        /** @var string */
        public $lastName;

        /** @var string */
        public $avatar;

        /** @var string */
        public $targetFeedIds;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;

        public function FullName() {
            return trim(FormHelper::RenderToForm($this->firstName . ' ' . $this->lastName));
        }
    }
?>