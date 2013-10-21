<?php
/** @var TargetFeed $object */

$__pageTitle = LocaleLoader::Translate( "vt.screens.publics-stat.main");

$__breadcrumbs = array(
     array( 'link' => Site::GetWebPath( "vt://publics-stat/" ) , 'title' => LocaleLoader::Translate( "vt.screens.publics-stat.main" ) )
);
?>
{increal:tmpl://vt/header.tmpl.php}
<div class="main">
    <div class="inner">
        <form method="post" target="_blank" action="{web:controls://get-publics-stat/}" enctype="multipart/form-data" id="data-form">
            {increal:tmpl://vt/elements/menu/breadcrumbs.tmpl.php}
            <div class="pagetitle">
                <h1>{$__pageTitle}</h1>
            </div>
            <?= FormHelper::FormHidden( 'action', BaseSaveAction::AddAction ); ?>
            <div data-row="type" class="row required">
                <label>{lang:vt.publics-stat.from}</label>
                <?= FormHelper::FormDate( 'from'); ?>
            </div>
            <div data-row="type" class="row required">
                <label>{lang:vt.publics-stat.to}</label>
                <?= FormHelper::FormDate( 'to'); ?>
            </div>
            <div data-row="type" class="row required">
                <label>{lang:vt.publics-stat.method}</label>
                <?= FormHelper::FormSelect( 'method', $methods, '', '', 'barter'); ?>
            </div>
            <label>Прежде чем бездумно жать на эту кнопку, убедитесь, что прошлые нажатия доработали(и файлы скачались)</label><br><br>
            <input type="submit" value="{lang:vt.publics-stat.get-stat}" name="subscribe" id="mc-embedded-subscribe" class="submit">

        </form>
    </div>
</div>
{increal:tmpl://vt/footer.tmpl.php}