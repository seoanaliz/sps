<?php
    /** @var SourceFeed $SourceFeed */
    $SourceFeed = $object;

    $prefix = "sourceFeed";

    if ( empty( $errors ) ) $errors = array();
	if ( empty( $jsonErrors ) ) $jsonErrors = '{}';

    if ( !empty($errors["fatal"] ) ) {
		?><h3 class="error"><?= LocaleLoader::Translate( 'errors.fatal.' . $errors["fatal"] ); ?></h3><?
	}

    $SourceFeed->targetFeedIds = explode(',', $SourceFeed->targetFeedIds);
?>
<div class="tabs">
	<?= FormHelper::FormHidden( 'selectedTab', !empty( $selectedTab ) ? $selectedTab : 0, 'selectedTab' ); ?>
    <ul class="tabs-list">
        <li><a href="#page-0">{lang:vt.common.commonInfo}</a></li>
    </ul>

    <div id="page-0" class="tab-page rows">
        <div data-row="title" class="row required">
            <label>{lang:vt.sourceFeed.title}</label>
            <?= FormHelper::FormInput( $prefix . '[title]', $SourceFeed->title, 'title', null, array( 'size' => 80 ) ); ?>
        </div>

        <div data-row="externalId" class="row required">
            <label>{lang:vt.common.externalId}</label>
            <?= FormHelper::FormInput( $prefix . '[externalId]', $SourceFeed->externalId, 'externalId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="useFullExport" class="row required">
            <label>{lang:vt.sourceFeed.useFullExport}</label>
            <?= FormHelper::FormCheckBox( $prefix . '[useFullExport]', null, 'useFullExport', null, $SourceFeed->useFullExport ); ?>
        </div>
        <div data-row="onlyOurs" class="row required">
            <label>{lang:vt.sourceFeed.useFullExport}</label>
            <?= FormHelper::FormCheckBox(  'onlyOurs', null, 'onlyOurs', null, $onlyOuers ); ?>
        </div>
        <div data-row="targetFeedIds" class="row">
            <label>{lang:vt.sourceFeed.targetFeedIds}</label>
            <?= FormHelper::FormSelectMultiple( 'targetFeedIds[]', $targetFeeds, 'targetFeedId', 'title', $SourceFeed->targetFeedIds, 'targetFeedIds', null, null, array('style' => 'height: 200px;') ) ?>
        </div>
        <div data-row="type" class="row required">
            <label>{lang:vt.sourceFeed.type}</label>
            <?= FormHelper::FormSelect( $prefix . '[type]', SourceFeedUtility::$Types, "", "", $SourceFeed->type, null, null, false ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.sourceFeed.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $SourceFeed->statusId, null, null, false ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
 