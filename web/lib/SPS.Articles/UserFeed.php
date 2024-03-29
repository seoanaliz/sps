<?php
/**
 * User: x100up
 * Date: 17.11.12 11:58
 * In Code We Trust
 */

/**
 * UserFeed
 *
 * @package SPS
 * @subpackage Articles
 */
class UserFeed
{
    /**
     * Роль редактор
     */
    const ROLE_EDITOR = 0;

    /**
     * Роль редактор
     */
    const ROLE_OWNER = 1;

    /**
     * Роль автор
     */
    const ROLE_AUTHOR = 2;

    /**
     * Роль администратор
     */
    const ROLE_ADMINISTRATOR = 3;

    /** @var int */
    public $vkId;

    /** @var int */
    public $targetFeedId;

    /** @var int */
    public $role;

    public function __construct( $vkId='', $targetFeedId='', $role='') {
        if( $vkId && $targetFeedId ) {
            $this->vkId = $vkId;
            $this->targetFeedId = $targetFeedId;
            $this->role = $role;
        }
    }
}
