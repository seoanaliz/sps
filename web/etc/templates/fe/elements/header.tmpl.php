<?
	if (!isset($__activeElement)) $__activeElement = NULL;

    /**
     * Manual set meta or reset of meta
     */
    $__sitePageTitle    = 'Socialboard';
    $__pageTitle        = !empty( $__pageTitle ) ? $__pageTitle : '';
    $__metaDescription  = !empty( $__metaDescription ) ? $__metaDescription : '';
    $__metaKeywords     = !empty( $__metaKeywords ) ? $__metaKeywords : '';
    $__imageAlt         = !empty( $__imageAlt ) ? $__imageAlt : '';

	/*
	 * Meta tags from MetaDetail object or Page object
	*/
	if ( !empty( $__metaDetail ) ) {
        if ( !empty( $__metaDetail->pageTitle ) )       $__pageTitle       = $__metaDetail->pageTitle;
        if ( !empty( $__metaDetail->metaDescription ) ) $__metaDescription = $__metaDetail->metaDescription;
        if ( !empty( $__metaDetail->metaKeywords) )     $__metaKeywords    = $__metaDetail->metaKeywords;
        if ( !empty( $__metaDetail->alt) )         		$__imageAlt        = $__metaDetail->alt;
    } else if( !empty( $__page ) ) {
        $__pageTitle = !empty( $__page->pageTitle ) ? $__page->pageTitle : ( $__page->title . ' | ' . $__sitePageTitle );
        
        if ( !empty( $__page->metaDescription ) ) $__metaDescription = $__page->metaDescription;
        if ( !empty( $__page->metaKeywords) )     $__metaKeywords    = $__page->metaKeywords;
    }

    /**
     * Default page title
     */
    $__pageTitle = !empty( $__pageTitle ) ? $__pageTitle : $__sitePageTitle;
	
    $cssFiles = array(
        AssetHelper::AnyBrowser => array(
            'js://fe/file-uploader/fileuploader.css',
            'css://fe/jquery.multiselect.css',
            'css://fe/jquery-ui.css',
            'css://common/common.css',
            'css://fe/main.css',
            'css://fe/custom.css',
            'css://fe/jquery.datepick.css',
            'js://ext/fancybox2/jquery.fancybox.css',
            'js://ext/fancybox2/helpers/jquery.fancybox-buttons.css',
        )
        , AssetHelper::IE7 => array()
    );

    $jsFiles = array(
        'js://fe/locale/'. LocaleLoader::$CurrentLanguage . '.js',
        'js://common/jquery-1.7.2.min.js',
        'js://common/jquery.easydate-0.2.4.js',
        'js://common/jquery.transit.js',
        'js://common/common.js',
        'js://common/class.js',
        'js://common/event.js',
        'js://common/model.js',
        'js://common/collection.js',
        'js://common/control.js',
        'js://fe/jquery-ui-1.8.18.custom.min.js',
        'js://ext/jquery.plugins/jquery.cookie.js',
        'js://ext/jquery.plugins/jquery.blockui.js',
        'js://ext/jquery.plugins/jquery.tmpl.min.js',
        'js://ext/jquery.plugins/jquery.scrollTo-min.js',
        'js://ext/fancybox2/jquery.fancybox.js',
        'js://ext/fancybox2/helpers/jquery.fancybox-buttons.js',
        'js://fe/jquery.ui.datepicker.js',
        'js://fe/jquery.datepick.js',
        'js://fe/jquery.datepick-ru.js',
        'js://fe/jquery.ui.slider.js',
        'js://fe/eventlist.js',
        'js://fe/file-uploader/fileuploader.js',
        'js://fe/jquery.Jcrop.min.js',
        'js://fe/jquery.maskedinput-1.3.js',
        'js://fe/jquery.multiselect.min.js',
        'js://fe/edit-post.js',
        'js://fe/files.js',
        'js://fe/LeftPanelWidget.js',
        'js://fe/RightPanelWidget.js',
        'js://fe/QueueWidget.js',
        'js://fe/App.js',
        'js://fe/Elements.js',
        'js://fe/templates.js',
        'js://fe/main.js',
    );

    CssHelper::Init( true );
    JsHelper::Init( true );

    CssHelper::PushGroups( $cssFiles );
    if( !empty( $cssFilesAdds ) ) {
        CssHelper::PushGroups( $cssFilesAdds );
    }

    JsHelper::PushFiles( $jsFiles );
    if( !empty( $jsFilesAdds ) ) {
        JsHelper::PushFiles( $jsFilesAdds );
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
        var appControlsRoot = '{web:appControls://editor/}';
        var vk_appId = <?= AuthVkontakte::$AppId ?>;
        var hostname = '<?= Site::$Host->GetHostname() ?>';
    </script>

    <title><?=$__pageTitle?></title>
    <meta name="keywords" content="{form:$__metaKeywords}" />
	<meta name="description" content="{form:$__metaDescription}" />
    <? if (!empty( $__params[SiteParamHelper::YandexMeta] ) ) { ?>
    <meta name='yandex-verification' content='<?= $__params[SiteParamHelper::YandexMeta]->value ?>' />
    <? } ?>
    <? if (!empty( $__params[SiteParamHelper::GoogleMeta] ) ) { ?>
    <meta name='google-site-verification' content='<?= $__params[SiteParamHelper::GoogleMeta]->value ?>' />
    <? } ?>
	<link rel="icon" href="{web:/favicon.ico}" type="image/x-icon" />
    <link rel="shortcut icon" href="{web:/favicon.ico}" type="image/x-icon" />
    <?= CssHelper::Flush(); ?>
</head>
<body>
