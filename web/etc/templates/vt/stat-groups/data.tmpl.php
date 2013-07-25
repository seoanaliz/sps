<?php
/** @var Group $object */

$prefix = "group";

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
        <div data-row="name" class="row">
            <label>{lang:vt.stat.name}</label>
            <?= FormHelper::FormInput( $prefix . '[name]', $object->name, 'name', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="slug" class="row">
            <label>{lang:vt.stat.slug}</label>
            <?= FormHelper::FormInput( $prefix . '[slug]', $object->slug, 'slug', null, array( 'size' => 80 ) ); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    var jsonErrors = {$jsonErrors};
</script>
<?php
$__useEditor = true;
?>
 