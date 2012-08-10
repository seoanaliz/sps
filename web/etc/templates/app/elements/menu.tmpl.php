<div class="menu" id="menu">
    <div class="item my" data-id="my">
        <div class="content">
            Мои публикации
            <span class="counter">
                <? if(!empty($authorCounter['total'])) { ?>
                    +{$authorCounter[total]}
                <? } ?>
            </span>
        </div>
    </div>
    <?
        $i = 0;
        foreach($targetFeeds as $targetFeed) {
            $i++;
            ?>{increal:tmpl://app/elements/menu-item.tmpl.php}<?
        }
    ?>
</div>
