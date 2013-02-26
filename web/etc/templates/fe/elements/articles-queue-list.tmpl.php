<?
/** @var $canEditQueue bool */
/** @var $grid array */
/** @var $articlesQueue array */

foreach ($grid as $gridItem) {
    $id = $gridItem['dateTime']->format('U');
    $isEmptyItem = empty($gridItem['queue']);
    ?>
    <div class="slot
        <?= !$canEditQueue || !empty($gridItem['blocked']) ? 'locked' : '' ?>
        <?= $isEmptyItem ? 'empty' : '' ?>"
         data-id="{$id}"
         data-grid-id="{$gridItem[gridLineId]}"
         data-grid-item-id="{$gridItem[gridLineItemId]}"
         data-start-date="<?= $gridItem['startDate']->format('d.m.Y') ?>"
         data-end-date="<?= $gridItem['endDate']->format('d.m.Y') ?>">
        <? if ($isEmptyItem) { ?>
            <? if ($canEditQueue) { ?>
                <div class="slot-header">
                    <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                    <span class="datepicker"></span>
                </div>
            <? } ?>
        <? } else { ?>
            <?
            $articleQueueId = $gridItem['queue']->articleQueueId;
            $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
            $delete_at = !empty($articlesQueue[$articleQueueId]->deleteAt) ? $articlesQueue[$articleQueueId]->deleteAt->modify('+1 minute')->defaultTimeFormat() : null;
            ?>
            <? if ($canEditQueue) { ?>
                <div class="slot-header">
                    <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                    <span class="datepicker"></span>
                    <span class="time-of-removal"></span>
                    <span class="time-of-remove"><?= $delete_at ? $delete_at : '' ?></span>
                    {increal:tmpl://fe/elements/articles-queue-item-header.tmpl.php}
                </div>
            <? } ?>
            <div class="post movable
                <?= !$canEditQueue || !empty($gridItem['blocked']) ? 'blocked' : '' ?>
                <?= !empty($gridItem['failed']) ? 'failed' : '' ?>"
                 data-id="{$articleQueueId}"
                 data-queue-id="{$articleQueueId}">
                <div class="content">
                    {increal:tmpl://fe/elements/articles-queue-item-content.tmpl.php}
                </div>
                <? if (empty($gridItem['blocked']) && $canEditQueue) { ?>
                    <div class="delete"></div>
                <? } ?>
            </div>
<!--            <div class="expanded-post">-->
<!--                <div class="content">-->
<!--                    {increal:tmpl://fe/elements/article-item-content.tmpl.php}-->
<!--                </div>-->
<!--            </div>-->
        <? } ?>
    </div>
<? } ?>
