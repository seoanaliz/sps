<?php
    /** @var Article $object */

    $prefix = "article";

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
        <div data-row="importedAt" class="row required">
            <label>{lang:vt.article.importedAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[importedAt]', $object->importedAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="sourceFeedId" class="row required">
            <label>{lang:vt.article.sourceFeedId}</label>
            <?= FormHelper::FormSelect( $prefix . '[sourceFeedId]', $sourceFees, "sourceFeedId", "title", $object->sourceFeedId, null, null, false ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.article.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
 