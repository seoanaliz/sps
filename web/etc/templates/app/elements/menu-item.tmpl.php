<?
    /** @var $targetFeed TargetFeed */
    /** @var $targetInfo array */
?>
<div class="item public" data-id="p{$targetFeed->targetFeedId}" data-empty="1">
    <div class="photo"><img src="<?= $targetInfo[$targetFeed->targetFeedId]['img'] ?>" alt="" /></div>
    <div class="content">
        <div class="text">{$targetFeed->title}</div>
        <? if(!empty($targetCounters[$targetFeed->targetFeedId]) && !empty($Editor)) { ?>
            <span class="counter">+<?= $targetCounters[$targetFeed->targetFeedId] ?></span>
        <? } ?>
    </div>
</div>