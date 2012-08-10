var Configs = {
    vkId: 4718705,
    appId: vk_appId,
    controlsRoot: controlsRoot
};

var cur = {
    dataUser: {}
};

$(document).ready(function() {
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

        t.rightColumn.on('selectList', function() {
            t.leftColumn.showDialog();
        });
        t.rightColumn.on('selectDialog', function() {
            t.leftColumn.showDialogList();
        });
    },

    initLeftColumn: function() {
        var t = this;

        return new Messages({
            el: '.left-column > .list',
            data: {
                list: Data.dialogs
            }
        });
    },

    initRightColumn: function() {
        var t = this;

        return new List({
            el: '.right-column > .list',
            data: {
                list: Data.lists
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

    showDialog: function() {
        alert(1);
    },

    showDialogList: function() {
        alert(2);
    },

    deleteMessage: function(id) {
    }
});

/*
 * List of dialogs
 */
var List = Widget.extend({
    template: LIST,

    events: {
        'click: .item > .title': 'selectList',
        'click: .public': 'selectDialog'
    },

    selectList: function(e) {
        this.trigger('selectList');
    },

    selectDialog: function(e) {
        this.trigger('selectDialog');
    }
});