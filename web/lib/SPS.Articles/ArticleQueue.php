<?php
/**
 * WTF MFD EG 1.6 [t:trunk]
 * Copyright (c) The 1ADW. All rights reserved.
 */

/**
 * ArticleQueue
 *
 * @package SPS
 * @subpackage Articles
 */
class ArticleQueue {

    /** @var int */
    public $articleQueueId;

    /** @var DateTimeWrapper */
    public $startDate;

    /** @var DateTimeWrapper */
    public $endDate;

    /** @var DateTimeWrapper */
    public $createdAt;

    /** @var DateTimeWrapper */
    public $sentAt;

    /** @var string */
    public $type;

    /** @var string */
    public $author;

    /** @var string */
    public $externalId;

    /** @var int */
    public $externalLikes;

    /** @var int */
    public $externalRetweets;

    /** @var int */
    public $articleId;

    /** @var Article */
    public $article;

    /** @var Author */
    public $articleAuthor;

    /** @var Author */
    public $articleQueueCreator;

    /** @var int */
    public $targetFeedId;

    /** @var TargetFeed */
    public $targetFeed;

    /** @var int */
    public $statusId;

    /** @var Status */
    public $status;

    /** @var DateTimeWrapper */
    public $deleteAt;

    /** @var boolean */
    public $isDeleted;

    /** @var boolean */
    public $collectLikes;

    /** @var DateTimeWrapper */
    public $protectTo;
}
?>