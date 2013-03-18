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
            html += 'Text' + Math.random() + '<br>';
        }

        if (!t.popup) {
            t.popup = new Box({
                title: 'Выберите сообщенства',
                html: html,
                buttons: [
                    {label: 'Отправить заявку'}
                ]
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