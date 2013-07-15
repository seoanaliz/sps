<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * GroupUser
     *
     * @package stat
     * @subpackage 
     */
    class GroupUser {

        /** @var int */
        public $groupId;

        /** @var string */
        public $vkId;

        /** @var int */
        public $sourceType;

        public function __construct( $groupId = null, $vkId = null, $sourceType = null) {
            if( $groupId && $vkId && $sourceType) {
                $this->groupId      = $groupId;
                $this->vkId         = $vkId;
                $this->sourceType   = $sourceType;
            }
        }
    }
?>