$(document).ready(function() {
    window.app = new App();
});

Control.root = controlsRoot;

App = Event.extend({
    init: function() {
        this.checkIsChildWindow();
        this.initPopup();
    },

    /**
     * После авторизации в ВК, нас переадресовыват
     * на нашу же страничку в попапе. Если мы
     * сейчас в попапе, то должны его закрыть
     */
    checkIsChildWindow: function() {
        if (window.parent && location.hash) {
            var accessToken = (location.hash.split('&')[0] || '').split('=')[1];
            if (accessToken) {
                $.cookie('accessToken', accessToken);
            }
            window.close();
        }
    },

    /**
     * Инициализация попапа со списком групп
     */
    initPopup: function() {
        var t = this;
        var $createApp = $('#create-app');

        if (t.isAlreadySent()) {
            $createApp.html('Ваша заявка рассматривается').addClass('disabled');
            $('.big-button-description').hide();
            return;
        }

        if (t.isLoginVK()) {
            t.updatePopup();
        }

        $createApp.on('click', function() {
            if (t.isLoginVK()) {
                if (!t.popup) {
                    t.updatePopup().success(function() {
                        t.showPopup();
                    });
                } else {
                    t.showPopup();
                }
            } else {
                t.loginVK();
            }
        });
    },

    /**
     * Получить группы и перерисовать попап с группами
     * @returns {Deferred}
     */
    updatePopup: function() {
        var t = this;
        var deferred = new Deferred();

        t.getVKGroups().success(function(response) {
            var groups = response.groups;
            var html = '';
            $.each(groups, function() {
                var group = this;
                if (group.gid) {
                    html += '<div class="group-row" data-id="' + group.gid + '">' +
                    '<div class="image"><img src="' + group.photo + '" /></div>' +
                    '<div class="title">' + group.name + '</div>' +
                    '</div>';
                }
            });

            if (!html) {
                html = '<div class="groups-empty">У вас нет администрируемых групп :(</div>';
            }

            if (!t.popup) {
                t.popup = new Box({
                    title: 'Выберите сообщества',
                    additionalClass: 'box-groups',
                    buttons: [
                        {label: 'Отправить заявку', onclick: function() {
                            t.sendGroups();
                        }}
                    ]
                });

                t.popup.$box.delegate('.group-row', 'mousedown', function() {
                    $(this).toggleClass('selected');
                });

                t.popup.$box.find('.actions').prepend('<div class="textarea-wrap">' +
                '<input type="email" name="email" class="email-input" placeholder="Введите свой e-mail..." />' +
                '</div>');
            }

            t.popup.setHTML(html);

            deferred.fireSuccess();
        }).error(function(error) {
            deferred.fireError(error);
        });

        return deferred;
    },

    /**
     * Показать попап с группами
     */
    showPopup: function() {
        var t = this;
        if (!t.popup) {
            return;
        }
        t.popup.show();
    },

    /**
     * Скрыть попап с группами
     */
    hidePopup: function() {
        var t = this;
        if (!t.popup) {
            return;
        }
        t.popup.hide();
    },

    /**
     * Отправить группы на сервер
     */
    sendGroups: function() {
        var t = this;
        if (!t.popup) {
            return;
        }
        var $groups = t.popup.$box.find('.group-row.selected');
        var $email = t.popup.$box.find('.email-input');
        var email = $email.val();
        var groups = [];

        $groups.each(function() {
            groups.push($(this).data('id'));
        });

        if (!groups.length) {
            return;
        }

        if (!email) {
            $email.focus();
            return;
        }

        t.popup.$box.find('.button').addClass('disabled');
        Control.call('saveNewUser', {
            email: email,
            publicIds: groups.join(',')
        }).always(function() {
            t.popup.$box.find('.button').removeClass('disabled');
        }).success(function() {
            t.popup.hide();
            $.cookie('alreadySent', 1);
            location.reload();
        });
    },

    /**
     * Уже отправил заявку
     * @returns {boolean}
     */
    isAlreadySent: function() {
        return !!$.cookie('alreadySent');
    },

    /**
     * Залогинен в ВК с помощью нашего приложения
     * @returns {boolean}
     */
    isLoginVK: function() {
        return !!$.cookie('accessToken');
    },

    /**
     * Открывает попап для логина в ВК
     */
    loginVK: function() {
        $.cookie('accessToken', '');
        var appId = window.vkAppId;
        var scope = 'offline,stats,groups';
        var loginUrl = 'https://oauth.vk.com/authorize' +
        '?client_id=' + appId +
        '&scope=' + scope +
        '&redirect_uri=' + location.href +
        '&display=popup' +
        '&response_type=token';
        windowOpen(loginUrl);
    },

    /**
     * Получить массив администрируемых групп
     * @returns {Deferred}
     */
    getVKGroups: function() {
        var t = this;
        var code =
        'return {' +
        'groups: API.groups.get({extended: 1, filter: "admin"})' +
        '};';

        return Control.callVK('execute', {code: code});
    }
});