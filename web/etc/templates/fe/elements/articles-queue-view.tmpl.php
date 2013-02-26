<?
    /** @var $articlesQueue ArticleQueue[] */
    $now = DateTimeWrapper::Now();

    foreach ($articlesQueue as $articleQueueItem) {
        $articleQueueId = $articleQueueItem->articleQueueId;
        $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
        if (empty($articleRecord)) continue;
        ?>
            <div class="slot">
                <div class="slot-header">
                    <span>&nbsp;<?= !empty($articleQueueItem->startDate) ? $articleQueueItem->startDate->defaultTimeFormat() : '' ?></span>

                    {increal:tmpl://fe/elements/articles-queue-item-header.tmpl.php}
                </div>
                <div class="post blocked <?= ($articleQueueItem->statusId != StatusUtility::Finished && $articleQueueItem->endDate <= $now) ? 'failed' : '' ?>">
                    <div class="content">
                        {increal:tmpl://fe/elements/articles-queue-item-content.tmpl.php}
                    </div>
                </div>
            </div>
        <?
    }
?>
