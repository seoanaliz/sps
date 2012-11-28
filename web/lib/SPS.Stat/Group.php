<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 08.11.12
 * Time: 13:03
 * To change this template use File | Settings | File Templates.
 */
class Group
{
    /** @var int*/
    public $group_id;

    /** @var string*/
    public $name;

    /** @var boolean*/
    public $general;

    /** @var int*/
    public $type;

    /** @var int[]*/
    public $users_ids;

    /** @var int*/
    public $created_by;

    /** @var int*/
    public $status;

    /** @var int*/
    public $source;
}
