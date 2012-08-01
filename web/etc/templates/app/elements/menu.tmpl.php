<div class="menu" id="menu">
    <div class="item my selected" data-id="my">
        <div class="content">
            Мои публикации
        </div>
    </div>
    <? foreach($targetFeeds as $targetFeed) { ?>
        {increal:tmpl://app/elements/menu-item.tmpl.php}
    <? } ?>
    <!--div class="item user">
        <div class="photo"><img src="http://vk.cc/Q2PuP" alt="" /></div>
        <div class="content">
            <div class="text">Artyom Kohver</div>
            <div class="description">General editor</div>
        </div>
    </div -->
</div>
