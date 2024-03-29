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
            <?= FormHelper::FormInput( 'vkId', $object->vkId, 'vkId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.author.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Common[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
        <div data-row="isBot" class="row">
            <label>{lang:vt.author.isBot}</label>
            <?= FormHelper::FormCheckBox($prefix . '[isBot]', null,'isBot', null, $object->isBot  ) ?>
        </div>
        <div data-row="postFromBot" class="row">
            <label>{lang:vt.author.postFromBot}</label>
            <?= FormHelper::FormCheckBox($prefix . '[postFromBot]', null,'postFromBot', null, $object->postFromBot  ) ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 