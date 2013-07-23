<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * InstObservedAccount
     *
     * @package SPS
     * @subpackage Instagram
     */
    class InstObservedAccount {

        /** @var int */
        public $id;

        /** @var string */
        public $name;

        /** @var string */
        public $link;

        /** @var string */
        public $avatara;

        /** @var int */
        public $status;

        public function __construct( $id = null, $name = null, $link = null, $avatara = null) {
            if( $id && $link ) {
                $this->id = $id;
                $this->name = $name;
                $this->link = $link;
                $this->avatara = $avatara;
            }
            $this->status = StatusUtility::Enabled;
        }
    }
?>