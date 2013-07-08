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
        <div data-row="publicId" class="row">
            <label>{lang:vt.banner.publicId}</label>
            <?= FormHelper::FormInput( $prefix . '[publicId]', $object->publicId, 'publicId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="platform" class="row">
            <label>{lang:vt.banner.platform}</label>
            <?= FormHelper::FormInput( $prefix . '[platform]', $object->platform, 'platform', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="bannerId" class="row">
            <label>{lang:vt.banner.bannerId}</label>
            <?= FormHelper::FormInput( $prefix . '[bannerId]', $object->bannerId, 'bannerId', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="prob" class="row">
            <label>{lang:vt.banner.prob}</label>
            <?= FormHelper::FormInput( $prefix . '[prob]', $object->prob, 'prob', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="imgUrl" class="row">
            <label>{lang:vt.banner.imgUrl}</label>
            <?= FormHelper::FormInput( $prefix . '[imgUrl]', $object->imgUrl, 'imgUrl', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="actionUrl" class="row">
            <label>{lang:vt.banner.actionUrl}</label>
            <?= FormHelper::FormInput( $prefix . '[actionUrl]', $object->actionUrl, 'actionUrl', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="active" class="row">
            <label>{lang:vt.banner.active}</label>
            <?= FormHelper::FormInput( $prefix . '[active]', $object->active, 'active', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 