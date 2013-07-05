<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * GroupEntry
     *
     * @package stat
     * @subpackage 
     */
    class GroupEntry {

        /** @var int */
        public $groupId;

        /** @var int */
        public $entryId;

        /** @var int */
        public $sourceType;

        public function __construct( $groupId = null, $entryId = null, $sourceType = null) {
            if( $groupId && $entryId && $sourceType) {
                $this->groupId      = $groupId;
                $this->entryId      = $entryId;
                $this->sourceType   = $sourceType;
            }
        }
    }
?>