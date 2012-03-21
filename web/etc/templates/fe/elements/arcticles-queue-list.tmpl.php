<?
    foreach ($grid as $gridItem) {
        ?>
        <div class="slot empty" data-id="0">
            <div class="time"><?= $gridItem['dateTime']->defaultFormat() ?></div>
            <div class="content"></div>
        </div>
        <?
    }
?>