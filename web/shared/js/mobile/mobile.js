$(document).ready(function() {
    window.app = new App();
});

App = Event.extend({
    init: function() {
        this.checkIsChildWindow();
        this.initPopup();
    },

    checkIsChildWindow: function() {
        if (window.parent && location.hash) {
            var accessToken = (location.hash.split('&')[0] || '').split('=')[1];
            if (accessToken) {
                $.cookie('accessToken', accessToken);
            }
            window.close();
        }
    },

    initPopup: function() {
        var t = this;

        if (t.isLoginVK()) {
            t.updatePopup();
        }

        $('#create-app').on('click', function() {
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
     * ...
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
                '<input type="email" class="email-input" placeholder="Введите свой e-mail..." />' +
                '</div>');
            }

            t.popup.setHTML(html);

            deferred.fireSuccess();
        }).error(function(error) {
            deferred.fireError(error);
        });

        return deferred;
    },

    showPopup: function() {
        var t = this;
        if (!t.popup) {
            return;
        }
        t.popup.show();
    },

    hidePopup: function() {
        var t = this;
        if (!t.popup) {
            return;
        }
        t.popup.hide();
    },

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

        if (groups.length && email) {
            t.popup.hide();
        }
    },

    /**
     * @returns {boolean}
     */
    isLoginVK: function() {
        return !!$.cookie('accessToken');
    },

    loginVK: function() {
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

        return t.callVK('execute', {code: code});
    },

    /**
     * ...
     * @param method
     * @param params
     * @returns {Deferred}
     */
    callVK: function(method, params) {
        var deferred = new Deferred();

        $.ajax({
            dataType: 'jsonp',
            url: 'https://api.vk.com/method/' + method,
            data: $.extend({access_token: $.cookie('accessToken')}, params)
        }).always(function(data) {
            if (data && data.response) {
                deferred.fireSuccess(data.response);
            } else {
                $.cookie('accessToken', '');
                deferred.fireError(data);
            }
        });

        return deferred;
    }
});