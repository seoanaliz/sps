<?
    /** @var $articlesQueue ArticleQueue[] */

    foreach ($articlesQueue as $articleQueueItem) {
        $articleQueueId = $articleQueueItem->articleQueueId;
        $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
        if (empty($articleRecord)) continue;
        ?>
    <div class="slot">
        <div class="time">
            <?= !empty($articleQueueItem->startDate) ? $articleQueueItem->startDate->defaultTimeFormat() : '' ?>
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
        <div class="post">
            <div class="content">
                {increal:tmpl://fe/elements/arcticles-queue-item-content.tmpl.php}
            </div>
        </div>
    </div>
    <?
    }
?>