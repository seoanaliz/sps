<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * Editor
     *
     * @package SPS
     * @subpackage Articles
     */
    class Editor {

        /** @var int */
        public $editorId;

        /** @var int */
        public $vkId;

        /** @var string */
        public $firstName;

        /** @var string */
        public $lastName;

        /** @var string */
        public $avatar;

        /** @var array */
        public $targetFeedIds;

        /** @var int */
        public $statusId;

        /** @var Status */
        public $status;

        public function FullName() {
            return trim(FormHelper::RenderToForm($this->firstName . ' ' . $this->lastName));
        }

        public function getName() {
            return $this->firstName . ' ' . $this->lastName;
        }
    }
?>