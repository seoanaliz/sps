<?
/** @var $canEditQueue bool */
/** @var $gridData array */
/** @var $articlesQueue array */

foreach ($gridData as $timestamp => $grid) {
    $queueDate = new DateTimeWrapper(date('d.m.Y', $timestamp));
    ?>
        {increal:tmpl://fe/elements/articles-queue-list.tmpl.php}
    <?
}
?>