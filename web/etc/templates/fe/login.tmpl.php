<?
    CssHelper::PushFile('css://common/common.less');
    CssHelper::PushFile('css://fe/login.css');
    CssHelper::Init( true );

    CssHelper::PushGroups( $cssFiles );
    if( !empty( $cssFilesAdds ) ) {
        CssHelper::PushGroups( $cssFilesAdds );
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= LocaleLoader::$HtmlEncoding ?>" />
    <title>Socialboard</title>
    <?= CssHelper::Flush(); ?>
</head>
<body>
    <div class="login-button"><a href="<?= $href ?>">Войти</a></div>
{increal:tmpl://fe/elements/footer.tmpl.php}