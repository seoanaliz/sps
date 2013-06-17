<?
/** @var $canEditQueue bool */
/** @var $grid array */
/** @var $articlesQueue ArticleQueue[] */
/** @var $queueDate DateTimeWrapper */
/** @var $queueDate DateTimeWrapper */
/** @var $queueDate DateTimeWrapper */
/** @var $articleRecords ArticleRecord[] */
/** @var $repostArticleRecords ArticleRecord[] */
?>

<div class="queue-page" data-timestamp="<?= $queueDate->format('U') ?>">
    <? if (!empty($queueDate)) { ?>
        <div class="queue-title">
            <?= DateTimeHelper::GetRelativeDateString($queueDate, false) ?>
            <? if ($queueDate >= ( new DateTimeWrapper(date('d.m.Y')) ) && $canEditQueue) { ?>
                <a class="add-button r">Добавить ячейку</a>
            <? } ?>
        </div>
    <? } ?>
    <? if (!empty($grid)) { ?>
        <? foreach ($grid as $gridItem) { ?>
            {increal:tmpl://fe/elements/articles-queue-list-item.tmpl.php}
        <? } ?>
    <? } else { ?>
        <div class="empty-queue">Пусто</div>
    <? } ?>
</div>
