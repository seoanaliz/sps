<?
    $appId = 2842244;
    $scope = 'wall,offline,groups,pages,albums,photos';
    $loginUrl = 'https://oauth.vk.com/authorize' .
        '?client_id=' . $appId .
        '&scope=' . $scope .
        '&redirect_uri=http://oauth.vk.com/blank.html' .
        '&display=popup' .
        '&response_type=token';
?>

{increal:tmpl://editors_login/elements/header.tmpl.php}
<style type="text/css">
    body {
        overflow: hidden !important;
    }
</style>
<div id="login" class="login">
    <button id="loginBtn" data-url="{$loginUrl}" class="button">Получить ключ доступа</button>
    <input id="accessToken" type="text" placeholder="Введите URL нового окна..." />
    <div class="hint"></div>
    <div class="result"></div>
</div>
{increal:tmpl://editors_login/elements/footer.tmpl.php}