<?
if (!isset($__activeElement)) $__activeElement = NULL;

/**
 * Manual set meta or reset of meta
 */
$__sitePageTitle    = 'Рейтинг сообществ ВКонтакте';
$__pageTitle        = !empty($__pageTitle) ? $__pageTitle : '';
$__metaDescription  = !empty($__metaDescription) ? $__metaDescription : '';
$__metaKeywords     = !empty($__metaKeywords) ? $__metaKeywords : '';
$__imageAlt         = !empty($__imageAlt) ? $__imageAlt : '';

/*
     * Meta tags from MetaDetail object or Page object
    */
if (!empty($__metaDetail)) {
    if (!empty($__metaDetail->pageTitle))       $__pageTitle       = $__metaDetail->pageTitle;
    if (!empty($__metaDetail->metaDescription)) $__metaDescription = $__metaDetail->metaDescription;
    if (!empty($__metaDetail->metaKeywords))    $__metaKeywords    = $__metaDetail->metaKeywords;
    if (!empty($__metaDetail->alt))         	$__imageAlt        = $__metaDetail->alt;
} else if(!empty($__page)) {
    $__pageTitle = !empty($__page->pageTitle) ? $__page->pageTitle : ($__page->title . ' | ' . $__sitePageTitle);

    if (!empty($__page->metaDescription))       $__metaDescription = $__page->metaDescription;
    if (!empty($__page->metaKeywords))          $__metaKeywords    = $__page->metaKeywords;
}

/**
 * Default page title
 */
$__pageTitle = !empty($__pageTitle) ? $__pageTitle : $__sitePageTitle;

$cssFiles = array(
    AssetHelper::AnyBrowser => array(
        'css://common/common.less',
        'css://st/main.less',
        'css://fe/custom.css',
   ),
    AssetHelper::IE7 => array(),
);

CssHelper::Init(false);

CssHelper::PushGroups($cssFiles);
if(!empty($cssFilesAdds)) {
    CssHelper::PushGroups($cssFilesAdds);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= LocaleLoader::$HtmlEncoding ?>" />
    <script type="text/javascript">
        document.documentElement.id = "js";
        var root = '{web:/}';
        var controlsRoot = '{web:controls://}';
        var vk_appId = <?= AuthVkontakte::$AppId ?>;
        var hostname = '<?= Site::$Host->GetHostname() ?>';
    </script>

    <title><?=$__pageTitle?></title>
    <meta name="keywords" content="{form:$__metaKeywords}" />
    <meta name="description" content="{form:$__metaDescription}" />
    <? if (!empty($__params[SiteParamHelper::YandexMeta])) { ?>
        <meta name='yandex-verification' content='<?= $__params[SiteParamHelper::YandexMeta]->value ?>' />
    <? } ?>
    <? if (!empty($__params[SiteParamHelper::GoogleMeta])) { ?>
        <meta name='google-site-verification' content='<?= $__params[SiteParamHelper::GoogleMeta]->value ?>' />
    <? } ?>
    <link rel="icon" href="{web:/shared/images/st/favicon.ico}" type="image/x-icon" />
    <link rel="shortcut icon" href="{web:/shared/images/st/favicon.ico}" type="image/x-icon" />
    <link rel="image_src" href="{web:/shared/images/st/stat.png}">
    <?= CssHelper::Flush(); ?>

    <script type="text/javascript">
        rank = <?= $rank ?>;
        entriesPrecache = <?= $entriesPrecache ?>;
        groupsPrecache = <?= $groupsPrecache ?>;
    </script>

    <?= JsHelper::includeCombined(array(
        'common/jquery-1.7.2.min.js',
        'fe/jquery-ui-1.8.18.custom.min.js',
    )); ?>

    <?= JsHelper::includeCombined(array(
        'common/common.js',
        'common/class.js',
        'common/deferred.js',
        'fe/jquery.ui.slider.js',
        'st/main.js',
        'st/events.js',
        'st/template.js',
    )); ?>

    <? if (AuthVkontakte::IsAuth()) { ?>
        <script src="http://vk.com/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
    <? } ?>
</head>
<body>