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
        public $user_id;

        /** @var int */
        public $timestamp;

        /** @var boolean */
        public $get_vip;

        public function __construct( $userId = null, $timestamp = null, $getVip = null) {
            if( $userId && $timestamp ) {
                $this->user_id      = $userId;
                $this->timestamp    = $timestamp;
                $this->get_vip      = (boolean) $getVip;
            }
        }
    }

?>