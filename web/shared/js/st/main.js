/**
 * Initialization
 */
var cur = {
    dataTable: {},
    dataAllTable: {},
    wallLoaded: null, // Сколько страниц загружено
    selectedListId: null, // Выбраный список

    etc: null
};

var Configs = {
    appId: vk_appId,
    tableLoadOffset: 40,
    maxRows: 1000,
    globalLoaderTimeout: 2000,

    etc: null
};

var DataUser = {};

$(document).ready(function() {
    VK.init({
        apiId: configs.appId,
        nameTransportPath: '/xd_receiver.htm'
    });
    getInitData();

    function getInitData() {
        var code =
            'return {' +
                'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]' +
            '};';
        VK.Api.call('execute', {code: code}, onGetInitData);
    }

    function onGetInitData(data) {
        var r;
        if (data.response) {
            r = data.response;
            DataUser = r.me;

            Events.fire('load_list', function(dataList) {
                Events.fire('load_table', cur.selectedListId, 0, Configs.tableLoadOffset, function(dataTable) {
                    cur.wallLoaded = 1;
                    updateList(dataList);
                    updateTable(dataTable);
                    $('#global-loader').fadeOut(200);
                });
            });
        }
    }
});

/*
var cur = {
    dataList: {},
    dataTable: {},
    selectedListId: null, // Выбраный список
    wallLoaded: null,

    etc: null
};

var globalData = {
    user: {},
    table: {},

    etc: null
};

$(document).ready(function() {
    VK.init({
        apiId: vk_appId,
        nameTransportPath: '/xd_receiver.htm'
    });
    getInitData();

    function getInitData() {
        var code =
            'return {' +
                'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]' +
            '};';
        VK.Api.call('execute', {code: code}, initVK);
    }
});

function initVK(data) {
    if (data.response) {
        var r = data.response;
        globalData.user = r.me;

        List.init('.tab-bar');
    }
}

var List = {
    container: null,

    init: function(container) {
        var t = this;
        t.container = (typeof container == 'string') ? $(container) : container;
        t.update();
        t.onChange($('.tab.selected', t.container));
    },

    update: function() {
        var t = this;
        var $container = this.container;
        t.getData(function(data) {
            $container.html(tmpl(LIST, {items: data}));
            t.updateEvents();
        });
    },

    onChange: function($item) {
        var itemId = $item.data('id');
        var $container = this.container;

        $container.find('.tab').removeClass('selected');
        $item.addClass('selected');

        Events.fire('load_table', itemId, 0, 9999, function(dataTable) {
            initTable(dataTable);
        });
    },

    updateEvents: function() {
        var t = this;
        var $container = this.container;

        (function() {
            $container.delegate('.tab', 'click', function() {
                t.onChange($(this));
            });
        })();

    },

    getData: function(callback) {
        Events.fire('load_list', function(dataList) {
            callback(dataList);
        });
    },

    etc: null
};
*/