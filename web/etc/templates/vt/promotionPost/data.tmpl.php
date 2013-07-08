<?php
    /** @var PromotionPost $object */

    $prefix = "promotionPost";

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
            <label>{lang:vt.promotionPost.publicId}</label>
            <?= FormHelper::FormInput( $prefix . '[publicId]', $object->publicId, 'publicId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="platform" class="row">
            <label>{lang:vt.promotionPost.platform}</label>
            <?= FormHelper::FormInput( $prefix . '[platform]', $object->platform, 'platform', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="headerText" class="row">
            <label>{lang:vt.promotionPost.headerText}</label>
            <?= FormHelper::FormInput( $prefix . '[headerText]', $object->headerText, 'headerText', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="imgUrl" class="row">
            <label>{lang:vt.promotionPost.imgUrl}</label>
            <?= FormHelper::FormInput( $prefix . '[imgUrl]', $object->imgUrl, 'imgUrl', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="text" class="row">
            <label>{lang:vt.promotionPost.text}</label>
            <?= FormHelper::FormInput( $prefix . '[text]', $object->text, 'text', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="actionText" class="row">
            <label>{lang:vt.promotionPost.actionText}</label>
            <?= FormHelper::FormInput( $prefix . '[actionText]', $object->actionText, 'actionText', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="actionUrl" class="row">
            <label>{lang:vt.promotionPost.actionUrl}</label>
            <?= FormHelper::FormInput( $prefix . '[actionUrl]', $object->actionUrl, 'actionUrl', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="index" class="row">
            <label>{lang:vt.promotionPost.index}</label>
            <?= FormHelper::FormInput( $prefix . '[index]', $object->index, 'index', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="active" class="row">
            <label>{lang:vt.promotionPost.active}</label>
            <?= FormHelper::FormInput( $prefix . '[active]', $object->active, 'active', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
        </div>
        <div data-row="showsCount" class="row">
            <label>{lang:vt.promotionPost.showsCount}</label>
            <?= FormHelper::FormInput( $prefix . '[showsCount]', $object->showsCount, 'showsCount', null, array( 'size' => 80 ) ); ?>
        </div>
	</div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
<?php
	$__useEditor = true;
?>
 