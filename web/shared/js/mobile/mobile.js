$(document).ready(function() {
    window.app = new App();
});

App = Event.extend({
    init: function() {
        this.initPopup();
    },

    initPopup: function() {
        var t = this;

        t.checkVKAuth();
        $('#create-app').on('click', function() {
            t.showPopup();
        });
    },

    showPopup: function() {
        var t = this;
        var html = '';

        $.each(t.groups, function() {
            var group = this;
            if (group.gid) {
                html += '<div class="group-row">' +
                '<div class="image"><img src="' + group.photo + '" /></div>' +
                '<div class="title">' + group.name + '</div>' +
                '</div>';
            }
        });

        if (!t.popup) {
            t.popup = new Box({
                title: 'Выберите сообщества',
                html: html,
                additionalClass: 'box-groups',
                buttons: [
                    {label: 'Отправить заявку'}
                ]
            });

            t.popup.$box.delegate('.group-row', 'mousedown', function() {
                $(this).toggleClass('selected');
            });
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

    checkVKAuth: function() {
        var t = this;
        var code =
        'return {' +
        'me: API.users.get({uids: API.getVariable({key: 1280}), fields: "photo"})[0],' +
        'groups: API.groups.get({extended: 1, filter: "admin"})' +
        '};';

        VK.init({
            apiId: window.vkAppId,
            nameTransportPath: '/xd_receiver.htm'
        });

        VK.Api.call('execute', {code: code}, function(data) {
            var response = data && data.response;
            if (response) {
                t.groups = response.groups;
                console.log(response);
            }
        });
    },

    getVKGroups: function() {

    }
});