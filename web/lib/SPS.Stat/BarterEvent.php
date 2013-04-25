<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.10.12
 * Time: 18:51
 * To change this template use File | Settings | File Templates.
 */
class BarterEvent
{
    /** @var int */
    public $barter_event_id;

    /** @var int*/
    public $barter_type;

    /** @var int*/
    public $status;

    /** @var string */
    public $barter_public;

    /** @var string */
    public $target_public;

    /** @var string */
    public $search_string;

    /** @var DateTimeWrapper */
    public $created_at;

    /** @var DateTimeWrapper */
    public $start_search_at;

    /** @var DateTimeWrapper */
    public $stop_search_at;

    /** @var DateTimeWrapper */
    public $posted_at;

    /** @var DateTimeWrapper */
    public $deleted_at;

    /** @var DateTimeWrapper */
    public $detected_at;

    /** @var int */
    public $barter_overlaps;

    /** @var int */
    public $start_visitors;

    /** @var int */
    public $end_visitors;

     /** @var int */
    public $start_subscribers;

    /** @var int */
    public $end_subscribers;

    /** @var string */
    public $post_id;

    /** @var boolean */
    public $standard_mark;

    /** @var int array*/
    public $groups_ids;

    /** @var int */
    public $creator_id;

    /** @var int array*/
    public $init_users;

    /** @var int */
    public $neater_subscribers;

    public function __clone()
    {
        $this->barter_event_id = null;
        $this->created_at = date ( 'Y-m-d H:i:s', time());
    }
}
?>