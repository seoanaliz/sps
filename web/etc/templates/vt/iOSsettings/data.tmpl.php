<?php
    /** @var IOSsetting $object */

    $prefix = "iOSsetting";

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
        <div data-row="minVersion" class="row">
            <label>{lang:vt.iOSsetting.minVersion}</label>
            <?= FormHelper::FormInput( $prefix . '[minVersion]', $object->minVersion, 'minVersion', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="curVersion" class="row">
            <label>{lang:vt.iOSsetting.curVersion}</label>
            <?= FormHelper::FormInput( $prefix . '[curVersion]', $object->curVersion, 'curVersion', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="shareText" class="row">
            <label>{lang:vt.iOSsetting.shareText}</label>
            <?= FormHelper::FormInput( $prefix . '[shareText]', $object->shareText, 'shareText', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="publicId" class="row">
            <label>{lang:vt.iOSsetting.publicId}</label>
            <?= FormHelper::FormInput( $prefix . '[publicId]', $object->publicId, 'publicId', null, array( 'size' => 80 ) ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 