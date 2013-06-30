<?php
/**
 * WTF MFD EG 1.6 [t:trunk]
 * Copyright (c) The 1ADW. All rights reserved.
 */

/**
 * Article
 *
 * @package SPS
 * @subpackage Articles
 */
class Article
{

    /**
     * На рассмотрение
     */
    const STATUS_REVIEW = 1;

    /**
     * Одобрена
     */
    const STATUS_APPROVED = 2;

    /**
     * Отклонена
     */
    const STATUS_REJECT = 3;

    public static function getStatuses()
    {
        return array(
            self::STATUS_REVIEW => 'Новые',
            self::STATUS_APPROVED => 'Одобренные',
            self::STATUS_REJECT => 'Отклоненные',
        );
    }

    /** @var int */
    public $articleId;

    /** @var DateTimeWrapper */
    public $importedAt;

    /** @var DateTimeWrapper */
    public $createdAt;

    /** @var DateTimeWrapper */
    public $queuedAt;

    /** @var DateTimeWrapper */
    public $sentAt;

    /** @var string */
    public $externalId;

    /** @var int */
    public $rate;

    /** @var int */
    public $sourceFeedId;

    /** @var SourceFeed */
    public $sourceFeed;

    /** @var int */
    public $targetFeedId;

    /** @var TargetFeed */
    public $targetFeed;

    /** @var int */
    public $authorId;

    /** @var Author */
    public $author;

    /** @var string */
    public $editor;

    /** @var bool */
    public $isCleaned;

    /** @var bool */
    public $isSuggested;

    /** @var int */
    public $statusId;

    /** @var Status */
    public $status;

    /**
     * Статус:
     * одобрена, отклонена, на рассмотрении
     * @var int
     */
    public $articleStatus;

    /** @var int */
    public $userGroupId;
}

?>