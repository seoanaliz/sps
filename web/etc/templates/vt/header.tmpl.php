<?php
    if ( empty( $__noMenu ) ) {
        $__noMenu = false;
    }

    $__breadcrumbs = !empty( $__breadcrumbs ) ? $__breadcrumbs : array();

    $__currentLang = ( class_exists( "LocaleLoader" ) ) ? LocaleLoader::$CurrentLanguage : "ru";

    if ( empty( $__pageTitle ) ) {
        $__pageTitle = ( class_exists( "LocaleLoader" ) ) ? LocaleLoader::Translate( "vt.common.title" ) : "Virtual Terminal";
    }

    /** Menu Structure */
    $__menu = array(
        "audit" => array(
            "title"  => "vt.screens.auditEvent.list"
            , "link" => "vt://"
        )
        , "articles" => array(
            "title"  => "vt.menu.articles"
            , "link" => "vt://articles/"
        )
        , "articles-queue" => array(
            "title"  => "vt.menu.articlesQueue"
            , "link" => "vt://article-queues/"
        )
        , "source-feeds" => array(
            "title"  => "vt.menu.sourceFeeds"
            , "link" => "vt://source-feeds/"
        )
        , "target-feeds" => array(
            "title"  => "vt.menu.targetFeeds"
            , "link" => "vt://target-feeds/"
            , "menu" => array(
                array(
                    "title"  => "vt.menu.publishers"
                    , "link" => "vt://publishers/"
                )
            )
        )
        , "authors" => array(
            "title"  => "vt.menu.authors"
            , "link" => "vt://authors/"
        )
        , "editors" => array(
            "title"  => "vt.menu.editors"
            , "link" => "vt://editors/"
        )

        , "site-params" => array(
            "title"  => "vt.menu.siteParams"
            , "link" => "vt://site-params/"
            , "menu" => array(
                array(
                    "title"  => "vt.menu.users"
                    , "link" => "vt://users/"
                ),
                array(
                    "title"  => "vt.daemons.header"
                    , "link" => "vt://daemons/list/"
                ),
                array(
                    "title"  => "vt.screens.daemonLock.list"
                    , "link" => "vt://daemons/"
                ), array(
                     "title"  => "vt.menu.metaDetails"
                    , "link" => "vt://meta-details/"
                )
            )            
        )
        ,"mobile-params" => array(
            "title"  => "vt.menu.mobParams"
            , "link" => "vt://moby-params/"
            , "menu" => array(
                array(
                    "title"  => "vt.menu.andSettings"
                    , "link" => "vt://androidSettings/"
                ),
                array(
                    "title"  => "vt.menu.iosSettings"
                    , "link" => "vt://iOSsetting/"
                ),
                array(
                    "title"  => "vt.menu.banners"
                    , "link" => "vt://banners/"
                ),
                array(
                    "title"  => "vt.menu.categories"
                    , "link" => "vt://categories/"
                ),
                array(
                    "title"  => "vt.menu.promPosts"
                    , "link" => "vt://promotionPost/"
                ),
            )
        )
        ,"stat" => array(
              "title"  => "vt.menu.stat"
            , "link"   => null
            , "menu"   => array(
                    array(
                        "title"  => "vt.menu.statGroups"
                      , "link" => "vt://stat-groups/"
                    ),
                    array(
                          "title"  => "vt.menu.publics-stat"
                        , "link"   => "vt://publics-stat/"
                    )
                )
            )
        , "exit" => array (
            "title"  => "vt.menu.exit"
            , "link" => "vt://login"
            , "menu" => array(
                array(
                    "title"  => "vt.menu.logout"
                    , "link" => "vt://login"
                )
                , array(
                    "title"  => "vt.menu.toSite"
                    , "link" => "/"
                )
                , array(
                    "title"  => "Приложение для авторов"
                    , "link" => "http://vk.com/app" . AuthVkontakte::$AuthorAppId
                    , "target" => "_blank"
                )
                , array(
                    "title"    => "vt.menu.toSiteNew"
                    , "link"   => "/"
                    , "target" => "_blank"
                )
            )
        )
    );

    $cssFiles = array(
        AssetHelper::AnyBrowser => array(
            'css://vt/common.css'
            , 'css://vt/tags.css'
            , 'css://vt/classes.css'
            , 'css://vt/layout.css'
            , 'css://vt/ui.css'
            , 'css://vt/custom.css'

            , 'js://ext/fancybox/jquery.fancybox-1.3.4.css'
        )
        , AssetHelper::IE7 => array(
            'css://vt/common-ie.css'
            , 'css://vt/tags-ie.css'
            , 'css://vt/classes-ie.css'
            , 'css://vt/layout-ie.css'
        )
    );

    $jsFiles = array(
        'js://ext/jquery/jquery.js'
        , 'js://ext/jquery.plugins/jquery.superfish.js'
        , 'js://ext/jquery.plugins/jquery.clearable.js'
        , 'js://ext/jquery.plugins/jquery.datetimepicker.js'
        , 'js://ext/jquery.plugins/jquery.maskedinput.js'
        , 'js://ext/jquery.plugins/jquery.confirmdialog.js'
        , 'js://ext/jquery.plugins/jquery.blockui.js'
        , 'js://ext/jquery.plugins/jquery.cookie.js'
        , 'js://ext/jquery.plugins/jquery.tmpl.min.js'
        , 'js://ext/jquery.ui/jquery-ui.js'

        , 'js://ext/fancybox/jquery.easing-1.3.pack.js'
        , 'js://ext/fancybox/jquery.fancybox-1.3.4.js'

        , 'js://vfs/vfsConstants.'. $__currentLang . '.js'

        , 'js://vt/locale/'. $__currentLang . '.js'
        , 'js://vt/script.js'
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
<html xmlns="http://www.w3.org/1999/xhtml" id="nojs">
<head>
	<title><?= $__pageTitle ?></title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?= LocaleLoader::$HtmlEncoding ?>" />

    <script type="text/javascript">
        document.documentElement.id = "js";
        var root = '{web:/}';
        var controlsRoot = '{web:controls://}';
    </script>
    <?= CssHelper::Flush(); ?>
    <?= JsHelper::Flush(); ?>
</head>
<body>
    <? if ( !$__noMenu ) { ?>
        {increal:tmpl://vt/elements/menu/menu.tmpl.php}
    <? } ?>