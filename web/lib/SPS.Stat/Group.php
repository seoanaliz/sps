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

    /**
     * название группы по умолчанию
     */
    const DEFAULT_GROUP_NAME   = 'default_group';
    /**
     * тип группы - обмен
     */
    const BARTER_GROUP      = 1;
    const MESSAGER_GROUP    = 2;
    const STAT_GROUP        = 3;


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

    /** @var int[]*/
    public $entries_ids;

}
