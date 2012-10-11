<?
    $appId = 3069029;
    $scope = 'wall,friends,offline,messages,stats,groups,pages,notes,albums,video,audio,photos';
    $loginUrl = 'https://oauth.vk.com/authorize' .
        '?client_id=' . $appId .
        '&scope=' . $scope .
        '&redirect_uri=http://oauth.vk.com/blank.html' .
        '&display=popup' .
        '&response_type=token';
?>

{increal:tmpl://im/elements/header.tmpl.php}
<div id="login" class="login">
    <a id="loginBtn" href="{$loginUrl}" class="button">Получить ключ доступа</a>
    <input id="accessToken" type="text" placeholder="Введите URL нового окна..." />
    <div class="hint"></div>
    <div class="result"></div>
</div>
{increal:tmpl://im/elements/footer.tmpl.php}