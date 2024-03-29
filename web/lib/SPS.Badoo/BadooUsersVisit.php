<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * BadooUsersVisit
     *
     * @package Untitled
     * @subpackage Badoo
     */
    class BadooUsersVisit {

        /** @var int */
        public $user_id;

        /** @var int */
        public $timestamp;

        public function __construct( $userId = null, $timestamp = null) {
            if( $userId && $timestamp ) {
                $this->user_id       = $userId;
                $this->timestamp    = $timestamp;
            }
        }
    }
?>