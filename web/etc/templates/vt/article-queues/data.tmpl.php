<?php
    /** @var ArticleQueue $object */

    $prefix = "articleQueue";

    if ( empty( $errors ) ) $errors = array();
	if ( empty( $jsonErrors ) ) $jsonErrors = '{}';

    if ( !empty($errors["fatal"] ) ) {
		?><h3 class="error"><?= LocaleLoader::Translate( 'errors.fatal.' . $errors["fatal"] ); ?></h3><?
	}
?>
<div class="tabs">
	<?= FormHelper::FormHidden( 'selectedTab', !empty( $selectedTab ) ? $selectedTab : 0, 'selectedTab' ); ?>
    <ul class="tabs-list">
        <li><a href="#page-0">{lang:vt.common.commonInfo}</a></li>
    </ul>

    <div id="page-0" class="tab-page rows">
        <div data-row="startDate" class="row required">
            <label>{lang:vt.articleQueue.startDate}</label>
            <?= FormHelper::FormDateTime( $prefix . '[startDate]', $object->startDate, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="endDate" class="row required">
            <label>{lang:vt.articleQueue.endDate}</label>
            <?= FormHelper::FormDateTime( $prefix . '[endDate]', $object->endDate, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="createdAt" class="row required">
            <label>{lang:vt.articleQueue.createdAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[createdAt]', $object->createdAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="sentAt" class="row">
            <label>{lang:vt.articleQueue.sentAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[sentAt]', $object->sentAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="articleId" class="row required">
            <label>{lang:vt.articleQueue.articleId}</label>
            <?= FormHelper::FormInput( $prefix . '[articleId]', $object->articleId, 'articleId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="targetFeedId" class="row required">
            <label>{lang:vt.articleQueue.targetFeedId}</label>
            <?= FormHelper::FormSelect( $prefix . '[targetFeedId]', $targetFees, "targetFeedId", "title", $object->targetFeedId, null, null, false ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.articleQueue.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
 