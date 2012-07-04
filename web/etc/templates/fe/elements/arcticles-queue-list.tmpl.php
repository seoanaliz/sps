<?
    foreach ($grid as $gridItem) {
        $id = $gridItem['dateTime']->format('U');
        if (empty($gridItem['queue'])) {
            ?>
                <div class="slot <?= empty($gridItem['blocked']) ? 'empty' : '' ?>" data-id="{$id}">
                    <div class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></div>
                    <div class="content"></div>
                </div>
            <?
        } else {
            $articleQueueId = $gridItem['queue']->articleQueueId;
            $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
            ?>
                <div class="slot <?= !empty($gridItem['blocked']) ? 'locked' : '' ?>" data-id="{$id}">
                    <div class="time">
                        <?= $gridItem['dateTime']->defaultTimeFormat() ?>
                        <? if (!empty($articleRecord->link)) { ?>
                            <span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>
                        <? } ?>
                        <? if (UrlParser::IsContentWithLink($articleRecord->content)) { ?>
                            <span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>
                        <? } ?>
                        <? if (UrlParser::IsContentWithHash($articleRecord->content)) { ?>
                            <span class="hash-span" title="Пост с хештэгом">#hash</span>
                        <? } ?>
                    </div>
                    <div class="post movable <?= !empty($gridItem['blocked']) ? 'blocked' : '' ?> <?= !empty($gridItem['failed']) ? 'failed' : '' ?>" data-id="{$articleQueueId}" data-queue-id="{$articleQueueId}">
                        <div class="content">
                            {increal:tmpl://fe/elements/arcticles-queue-item-content.tmpl.php}
                        </div>
                        <? if(empty($gridItem['blocked'])) {?>
                            <div class="spr delete"></div>
                        <? } ?>
                    </div>
                </div>
            <?
        }
    }
?>