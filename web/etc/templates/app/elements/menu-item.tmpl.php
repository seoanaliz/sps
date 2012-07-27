<?
    /** @var $targetFeed TargetFeed */
    /** @var $targetInfo array */
?>
<div class="item public" data-id="p{$targetFeed->targetFeedId}" data-empty="<?= !in_array($targetFeed->targetFeedId, $targetFeedIdsWithPosts) ? '1' : '0' ?>">
    <div class="photo"><img src="<?= $targetInfo[$targetFeed->targetFeedId]['img'] ?>" alt="" /></div>
    <div class="content">
        <div class="text">{$targetFeed->title}</div>
        <? if ($counter) { ?>
        <span class="counter">+{$counter}</span>
        <? } ?>
    </div>
</div>