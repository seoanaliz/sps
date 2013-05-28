<?php
    /**
     * WTF MFD EG 1.6 [t:trunk]
     * Copyright (c) The 1ADW. All rights reserved.
     */

    /**
     * StatUsersAuthority
     *
     * @package stat
     * @subpackage 
     */
    class StatUsersAuthority {

        /**
         * Роль редактор
         */
        const STAT_ROLE_GUEST = 1;

        /**
         * Роль редактор
         */
        const STAT_ROLE_USER  = 2;

        /**
         * Роль редактор
         */
        const STAT_ROLE_EDITOR = 3;

        /**
         * Роль администратор
         */
        const STAT_ROLE_ADMIN  = 4;


        /** @var int */
        public $user_id;

        /** @var int */
        public $source;

        /** @var int */
        public $rank;
    }
?>