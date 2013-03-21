$(document).ready(function() {
    var newWindow;

    $('#loginBtn').click(function() {
        newWindow = windowOpen($(this).attr('href'));
        $('#accessToken').fadeIn(200);

        var $windowHint = $('.window-hint');
        if (!$windowHint.length) $windowHint = $('<div>').addClass('window-hint');
        $windowHint.show().html('Где-то здесь должно быть окно. Нажмите Разрешить, если вы не устанавливали наше приложение, а затем скопируйте всё содержимое адресной строки этого окна.').css({
            top: top,
            left: left - 160,
            width: 150
        });
        $('#login').append($windowHint);
        $(window).focus(function() {
            var $windowHint = $('.window-hint');
            $windowHint.fadeOut(200);
            newWindow.close();
        });

        return false;
    });

    (function() {
        $('#accessToken').bind('keyup blur', function(e) {
            var $result = $('.result');
            var $hint = $('.hint');
            var $button = $('.button');
            var accessToken = getURLParameter('access_token', $(this).val());

            if (accessToken != 'null') {
                $hint.html('Нажмите Enter, чтобы сохранить ключ доступа');
            } else {
                $hint.html('Ключ доступа не найден :(');
            }

            if (e.keyCode && e.keyCode != KEY.ENTER) return;
            if (accessToken == 'null') {
                return;
            }

            $(this).hide();
            $hint.hide();
            $result
                .show()
                .addClass('success')
                .html('Ключ доступа успешно сохранен')
            ;
            $button.slideUp(200);
            onSuccess(accessToken);
        });
    })();
});

function onSuccess(token) {
    $.cookie('token', token, {path: '/', expires: 30});
    Events.fire('add_user', token, function() {
        window.location = '/' + decodeURIComponent(location.search.substr(1));
    });
}
