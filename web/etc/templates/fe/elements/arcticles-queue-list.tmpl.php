<?
    foreach ($grid as $gridItem) {
        $id = $gridItem['dateTime']->format('U');
        $delete_at = !empty($articlesQueue[$articleQueueId]->deleteAt) ? $articlesQueue[$articleQueueId]->deleteAt->defaultTimeFormat() : 0;
        if (empty($gridItem['queue'])) {
            ?>
                <div class="slot <?= empty($gridItem['blocked']) ? 'empty' : '' ?>"
                     data-id="{$id}"
                     data-grid-id="{$gridItem[gridLineId]}"
                     data-grid-item-id="{$gridItem[gridLineItemId]}"
                     data-start-date="<?= $gridItem['startDate']->format('d.m.Y') ?>"
                     data-end-date="<?= $gridItem['endDate']->format('d.m.Y') ?>">
                    <div class="slot-header">
                        <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                        <span class="datepicker"></span>
                        <span class="time-of-removal"></span>
                        <span class="time-of-remove"><?= $delete_at ? $delete_at : '' ?></span>
                    </div>
                </div>
            <?
        } else {
            $articleQueueId = $gridItem['queue']->articleQueueId;
            $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
            ?>
                <div class="slot <?= !empty($gridItem['blocked']) ? 'locked' : '' ?>"
                     data-id="{$id}"
                     data-grid-id="{$gridItem[gridLineId]}"
                     data-grid-item-id="{$gridItem[gridLineItemId]}"
                     data-start-date="<?= $gridItem['startDate']->format('d.m.Y') ?>"
                     data-end-date="<?= $gridItem['endDate']->format('d.m.Y') ?>">
                    <? if ($canEditQueue): ?>
                    <div class="slot-header">
                        <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                        <span class="datepicker"></span>
                        <span class="time-of-removal"></span>
                        <span class="time-of-remove"><?= $delete_at ? $delete_at : '' ?></span>
                        {increal:tmpl://fe/elements/arcticles-queue-item-header.tmpl.php}
                    </div>
                    <? endif; ?>
                    <div class="post movable <?= !empty($gridItem['blocked']) ? 'blocked' : '' ?> <?= !empty($gridItem['failed']) ? 'failed' : '' ?>"
                         data-id="{$articleQueueId}"
                         data-queue-id="{$articleQueueId}">
                        <div class="content">
                            {increal:tmpl://fe/elements/arcticles-queue-item-content.tmpl.php}
                        </div>
                        <? if(empty($gridItem['blocked']) && $canEditQueue) {?>
                            <div class="spr delete"></div>
                        <? } ?>
                    </div>
                </div>
            <?
        }
    }
?>
