var Configs = {
    appId: vk_appId,
    controlsRoot: controlsRoot
};

var cur = {
    dataUser: {}
};

$(document).ready(function() {
    VK.init({
        apiId: Configs.appId,
        nameTransportPath: '/xd_receiver.htm'
    });

    var code =
        'return {' +
            'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]' +
        '};';

    VK.Api.call('execute', {code: code}, function(data) {
        if (data.response) {
            var r = data.response;
            cur.dataUser = r.me;
        }
    });

    new IM({
        el: '#main',
        template: MAIN
    });
});

/*
* Instant Messenger
*/
var IM = Widget.extend({
    leftColumn: null,
    rightColumn: null,

    run: function() {
        var t = this;
        t._super();

        t.leftColumn = t.initLeftColumn();
        t.rightColumn = t.initRightColumn();

        t.leftColumn.on('delete', function() {
            t.rightColumn.go();
        });
    },

    initLeftColumn: function() {
        var t = this;

        return new Messages({
            el: '.left-column > .list',
            data: {
                list: [1,2,3]
            }
        });
    },

    initRightColumn: function() {
        var t = this;

        return new List({
            el: '.right-column > .list',
            data: {
                list: [1,2,3,4,5,6]
            }
        });
    }
});

/*
* List of messages
*/
var Messages = Widget.extend({
    template: MESSAGES,

    events: {
        'click: .message': 'deleteMessage'
    },

    showList: function() {

    },

    showDialog: function(id) {
    },

    deleteMessage: function(id) {
        this.trigger('delete');
    }
});

/*
 * List of dialogs
 */
var List = Widget.extend({
    template: LIST,

    go: function() {
        alert('go!');
    }
});