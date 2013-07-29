<?php
    /** @var Category $object */

    $prefix = "category";

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
        <div data-row="index" class="row">
            <label>{lang:vt.mobile.index}</label>
            <?= FormHelper::FormInput( $prefix . '[index]', $object->index, 'index', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="publicId" class="row">
            <label>{lang:vt.mobile.publicId}</label>
            <?= FormHelper::FormInput( $prefix . '[publicId]', $object->publicId, 'publicId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="name" class="row">
            <label>{lang:vt.mobile.name}</label>
            <?= FormHelper::FormInput( $prefix . '[name]', $object->name, 'name', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="mask" class="row">
            <label>{lang:vt.mobile.mask}</label>
            <?= FormHelper::FormInput( $prefix . '[mask]', $object->mask, 'mask', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>

	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 