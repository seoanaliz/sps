$(document).ready(function() {
    window.app = new App();
});

App = Event.extend({
    init: function() {
        VK.init({
            apiId: window.vkAppId,
            nameTransportPath: '/xd_receiver.htm'
        });

        this.isAuthorizedInVK = null;
        this.initPopup();
    },

    initPopup: function() {
        var t = this;

        t.checkVKAuth().success(function() {
            t.isAuthorizedInVK = true;
            t.updatePopup().success(function() {
                t.showPopup();
            });
        });

        $('#create-app').on('click', function() {
            if (t.isAuthorizedInVK) {
                t.showPopup();
            } else {
                t.loginVK();

                VK.Observer.subscribe('auth.login', function() {
                    VK.Observer.unsubscribe('auth.login');

                    t.isAuthorizedInVK = true;
                    t.updatePopup().success(function() {
                        t.showPopup();
                    });
                });
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
            console.log(groups, email);
            t.popup.hide();
        }
    },

    /**
     * ...
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
     * ...
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
        var deferred = new Deferred();
        var code =
        'return {' +
        'groups: API.groups.get({extended: 1, filter: "admin"})' +
        '};';

        VK.Api.call('execute', {code: code}, function(data) {
            if (data && data.response) {
                deferred.fireSuccess(data.response);
            } else {
                deferred.fireError(data);
            }
        });

        return deferred;
    }
});