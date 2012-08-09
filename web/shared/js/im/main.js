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

        t.leftColumn.on('delete', function() {
            t.rightColumn.go();
        });
        t.rightColumn.on('selectDialog', function() {

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

    events: {
        'click: .item': 'selectItem'
    },

    selectItem: function(e) {
        console.log(e);
    },

    go: function() {
        alert('go!');
    }
});