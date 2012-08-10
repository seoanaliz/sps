<div class="menu" id="menu">
    <div class="item my selected" data-id="my">
        <div class="content">
            Мои публикации
            <span class="counter">
                <? if(!empty($authorCounter)) { ?>
                    +{$authorCounter}
                <? } ?>
            </span>
        </div>
    </div>
    <? foreach($targetFeeds as $targetFeed) { ?>
        {increal:tmpl://app/elements/menu-item.tmpl.php}
    <? } ?>
</div>
