<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * BadooUsersVip
     *
     * @package Untitled
     * @subpackage Badoo
     */
    class BadooUsersVip {

        /** @var int */
        public $userId;

        /** @var int */
        public $timestamp;

        /** @var int */
        public $getVip;

        public function __construct( $userId = null, $timestamp = null, $getVip = null) {
            if( $userId && $timestamp && $getVip ) {
                $this->userId       = $userId;
                $this->timestamp    = $timestamp;
                $this->getVip       = $getVip;
            }
        }
    }
?>