<?php
    /** @var Author $object */

    $prefix = "author";

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
        <div data-row="vkId" class="row required">
            <label>{lang:vt.author.vkId}</label>
            <?= FormHelper::FormInput( $prefix . '[vkId]', $object->vkId, 'vkId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="firstName" class="row">
            <label>{lang:vt.author.firstName}</label>
            <?= FormHelper::FormInput( $prefix . '[firstName]', $object->firstName, 'firstName', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="lastName" class="row">
            <label>{lang:vt.author.lastName}</label>
            <?= FormHelper::FormInput( $prefix . '[lastName]', $object->lastName, 'lastName', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="avatar" class="row">
            <label>{lang:vt.author.avatar}</label>
            <?= FormHelper::FormInput( $prefix . '[avatar]', $object->avatar, 'avatar', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="targetFeedIds" class="row">
            <label>{lang:vt.author.targetFeedIds}</label>
            <?= FormHelper::FormEditor( $prefix . '[targetFeedIds]', $object->targetFeedIds, 'targetFeedIds', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.author.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 