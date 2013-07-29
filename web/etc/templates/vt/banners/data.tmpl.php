<?php
    /** @var Banner $object */

    $prefix = "banner";

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
        <div data-row="bannerId" class="row">
            <label>{lang:vt.mobile.bannerId}</label>
            <?= FormHelper::FormInput( $prefix . '[bannerId]', $object->bannerId, 'bannerId', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="publicId" class="row">
            <label>{lang:vt.mobile.publicId}</label>
            <?= FormHelper::FormInput( $prefix . '[publicId]', $object->publicId, 'publicId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="prob" class="row">
            <label>{lang:vt.mobile.prob}</label>
            <?= FormHelper::FormInput( $prefix . '[prob]', $object->prob, 'prob', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="imgUrl" class="row">
            <label>{lang:vt.mobile.imgUrl}</label>
            <?= FormHelper::FormInput( $prefix . '[imgUrl]', $object->imgUrl, 'imgUrl', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="actionUrl" class="row">
            <label>{lang:vt.mobile.actionUrl}</label>
            <?= FormHelper::FormInput( $prefix . '[actionUrl]', $object->actionUrl, 'actionUrl', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="platform" class="row">
            <label>{lang:vt.mobile.platform}</label>
            <?= FormHelper::FormSelect( $prefix . '[platform]',array('ios' => 'ios', 'android' => 'android'),null, null, $object->platform, null, null, true ); ?>
        </div>
        <div data-row="active" class="row">
            <label>{lang:vt.mobile.active}</label>
            <?= FormHelper::FormSelect( $prefix . "[active]", array('on' => 'on', 'off' => 'off'),null, null, $object->active, null, null, true ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 