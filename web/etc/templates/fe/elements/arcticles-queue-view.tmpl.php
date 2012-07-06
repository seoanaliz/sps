<?
    /** @var $articlesQueue ArticleQueue[] */

    foreach ($articlesQueue as $articleQueueItem) {
        $articleQueueId = $articleQueueItem->articleQueueId;
        $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
        if (empty($articleRecord)) continue;
        ?>
            <div class="slot">
                <div class="slot-header">
                    <span><?= !empty($articleQueueItem->startDate) ? $articleQueueItem->startDate->defaultTimeFormat() : '' ?></span>

                    {increal:tmpl://fe/elements/arcticles-queue-item-header.tmpl.php}
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