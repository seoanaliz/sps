<?
if (!isset($__activeElement)) $__activeElement = null;

/**
 * Manual set meta or reset of meta
 */
$__sitePageTitle    = 'Рассмотрение заявок';
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
        'css://common/common.css',
        'css://st/main.css',
    ),
    AssetHelper::IE7 => array(),
);

$jsFiles = array(
    'js://common/jquery-1.7.2.min.js',
    'js://ext/jquery.plugins/jquery.cookie.js',
    'js://common/common.js',
    'js://common/class.js',
    'js://common/deferred.js',
    'js://common/control.js',
    'js://mobile/admin.js',
);

CssHelper::Init(false);
JsHelper::Init(true);

CssHelper::PushGroups($cssFiles);
if (!empty($cssFilesAdds)) {
    CssHelper::PushGroups($cssFilesAdds);
}

JsHelper::PushFiles($jsFiles);
if (!empty($jsFilesAdds)) {
    JsHelper::PushFiles($jsFilesAdds);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= LocaleLoader::$HtmlEncoding ?>" />
    <title><?=$__pageTitle?></title>
    <meta name="keywords" content="{form:$__metaKeywords}" />
    <link rel="icon" href="{web:/favicon.ico}" type="image/x-icon" />
    <link rel="shortcut icon" href="{web:/favicon.ico}" type="image/x-icon" />
    <?= CssHelper::Flush(); ?>
    <?= JsHelper::Flush(); ?>
</head>
<body>
<div id="global-loader"></div>
<div id="main">
    <div class="header">
        <div class="tab-bar">
            <div id="reviewing" class="tab selected">Заявки на рассмтрении</div>
            <div id="approved" class="tab">Одобренные</div>
            <div id="rejected" class="tab">Отклоненные</div>
        </div>
    </div>
    <div class="content">
        <div class="table" id="table">
            <!--  ROWS  -->
            <div class="header">
                <div class="row">
                    <div class="column column4">Имя</div>
                    <div class="column column6">Паблики</div>
                </div>
            </div>
            <div class="body"></div>
        </div>
        <div id="load-more-table">Показать больше</div>
    </div>
</div>
<div id="go-to-top">Наверх</div>
<script src="http://vk.com/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
<script type="text/javascript">
    var controlsRoot = '{web:controls://}';
    var vkAppId = <?= AuthVkontakte::$AppId ?>;
    VK.init({
        apiId: window.vkAppId,
        nameTransportPath: '/xd_receiver.htm'
    });
</script>
</body>
</html>