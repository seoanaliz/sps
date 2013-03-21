$(document).ready(function() {
    window.app = new App();
});

Control.root = controlsRoot;

App = Event.extend({
    init: function() {
        VK.init({
            apiId: window.vkAppId,
            nameTransportPath: '/xd_receiver.htm'
        });

        this.initPopup();
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

        t.checkVKAuth().success(function() {
            t.updatePopup();
        });

        $createApp.on('click', function() {
            t.checkVKAuth().success(function() {
                if (!t.popup) {
                    t.updatePopup().success(function() {
                        t.showPopup();
                    });
                } else {
                    t.showPopup();
                }
            }).error(function() {
                t.loginVK();

                VK.Observer.subscribe('auth.login', function() {
                    VK.Observer.unsubscribe('auth.login');

                    t.isAuthorizedInVK = true;
                    t.updatePopup().success(function() {
                        t.showPopup();
                    });
                });
            });
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
        }).success(function(data) {
            if (data && data.success) {
                t.popup.hide();
                $.cookie('alreadySent', 1);
                location.reload();
            } else {
                t.showErrorBox(data && data.message);
                console.log(data);
            }
        }).error(function() {
            t.showErrorBox();
        });
    },

    /**
     * @param {string=} text
     */
    showErrorBox: function(text) {
        var t = this;
        if (!t.errorPopup) {
            t.errorPopup = new Box({
                title: 'Ошибка'
            });
        }
        t.errorPopup.setHTML(text || 'Произошла ошибка. Пожалуйста, попробуйте еще раз чуть позже').show();
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
     * @returns {Deferred}
     */
    checkVKAuth: function() {
        var deferred = new Deferred();
        VK.Auth.getLoginStatus(function(data) {
            if (data.session) {
                deferred.fireSuccess();
            } else {
                deferred.fireError();
            }
        });
        return deferred;
    },

    /**
     * Открывает попап для логина в ВК
     * @returns {Deferred}
     */
    loginVK: function() {
        var deferred = new Deferred();
        VK.Auth.login(function() {
            deferred.fireSuccess();
        });
        return deferred;
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

        return Control.callVKByOpenAPI('execute', {code: code});
    }
});