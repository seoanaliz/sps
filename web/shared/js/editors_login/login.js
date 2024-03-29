$(document).ready(function() {
    var newWindow;

    $('#loginBtn').click(function() {
        newWindow = windowOpen($(this).data('url'));
        $('#accessToken').fadeIn(200);

        var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
        var screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
        var outerWidth = $(window).width();
        var width = 400;
        var top = parseInt(screenY + 280);
        var left = parseInt(screenX + ((outerWidth - width) / 2));
        var $windowHint = $('.window-hint');
        if (!$windowHint.length) {
            $windowHint = $('<div>').addClass('window-hint');
        }
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
    simpleAjax('saveEdtiorAt', {
        access_token: token
    }).success(function() {
        window.location = '/' + decodeURIComponent(location.search.substr(1));
    }).error(function(error) {
        new Box({title: 'Ошибка', html: 'Произошла ошибка при сохранении токена'}).show();
        throw error;
    });
}
