$(document).ready(function() {
    window.app = new App();
});

App = Event.extend({
    init: function() {
        this.initPopup();
    },

    initPopup: function() {
        var t = this;

        $('#create-app').on('click', function() {
            t.showPopup();
        });
    },

    showPopup: function() {
        var t = this;

        var html = '';
        for (var i = 0; i < 100; i++) {
            html += '<div class="group-row">' +
            '<div class="image"><img src="http://vk.com/images/camera_b.gif" /></div>' +
            '<div class="title">' + Math.random() + '</div>' +
            '</div>';
        }

        if (!t.popup) {
            t.popup = new Box({
                title: 'Выберите сообщенства',
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
        VK.init({
            apiId: window.vkAppId,
            nameTransportPath: '/xd_receiver.htm'
        });

        var code =
        'return {' +
        'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]' +
        '};';

        VK.Api.call('execute', {code: code}, function(data) {
            var response = data && data.response;
            if (response) {
                console.log(response);
            }
        });
    },

    getVKGroups: function() {

    }
});