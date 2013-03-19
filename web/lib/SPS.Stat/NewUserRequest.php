<?php

/**
 * NewUserRequest
 *
 * @package SPS
 * @subpackage Stat
 */
class NewUserRequest {

    /** @var int */
    public $newUserRequestId;

    /** @var string */
    public $vkId;

    /** @var string */
    public $email;

    /** @var array */
    public $publicIds;

    /** @var int */
    public $statusId;

    /** @var DateTimeWrapper */
    public $createdAt;
}
?>