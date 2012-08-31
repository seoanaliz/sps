$(document).ready(function() {
    var newWindow;

    $('#loginBtn').click(function() {
        var width = 400;
        var height = 100;
        var top = window.screen.height / 3 - height / 2;
        var left = window.screen.width / 2 - width / 2;
        var params = {
            top: top,
            left: left,
            width: width,
            height: height,
            resizable: 'yes',
            scrollbars: 'yes',
            status: 'yes'
        };
        var paramsStr = $.param(params).split('&').join(',');
        newWindow = window.open($(this).attr('href'), 'VK', paramsStr);
        $('#accessToken').fadeIn(200);

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
            newWindow.close();
            $button.slideUp(200);
            onSuccess(accessToken);
        });
    })();
});

function onSuccess(token) {
    $.cookie('token', token, {path: '/', expires: 30});
    location.replace('/im/');
}