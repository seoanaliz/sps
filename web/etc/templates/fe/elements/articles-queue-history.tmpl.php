<?
    /** @var $articlesQueue ArticleQueue[] */

    foreach ($articlesQueue as $articleQueueItem) {
        $articleQueueId = $articleQueueItem->articleQueueId;
        $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
        if (empty($articleRecord)) continue;
        ?>
            <div class="slot locked">
                <div class="slot-header">
                    <span>&nbsp;<?= !empty($articleQueueItem->sentAt) ? $articleQueueItem->sentAt->defaultTimeFormat() : $articleQueueItem->startDate->modify('+30 seconds')->defaultTimeFormat() ?></span>

                    {increal:tmpl://fe/elements/articles-queue-item-header.tmpl.php}
                </div>
                <div class="post blocked <?= empty($articleQueueItem->sentAt) ? 'failed' : '' ?>">
                    <div class="content">
                        {increal:tmpl://fe/elements/articles-queue-item-content.tmpl.php}
                    </div>
                </div>
            </div>
        <?
    }
?>