var timestampValue;
var sourceFeedIdValue;
var targetFeedIdValue;

function getCurrentValues() {
    try {
        timestampValue = $("#calendar").datepicker("getDate").getTime() / 1000;
    } catch (ex) {
        timestampValue = null;
    }

    sourceFeedIdValue = $(".left-panel .drop-down").data("selected");
    targetFeedIdValue = $(".right-panel .drop-down").data("selected");
}

function loadArticles() {
    getCurrentValues();

    if (!sourceFeedIdValue) {
        $('.newpost').hide();
        return;
    }

    $('.newpost').show();

    //clean and load left column
    $.ajax({
        url: controlsRoot + 'arcticles-list/',
        dataType : "html",
        data: {
            sourceFeedId: sourceFeedIdValue
        },
        success: function (data) {
            $('div#wall').html(data);
        }
    });
}

function loadQueue() {
    getCurrentValues();

    if (!targetFeedIdValue) {
        return;
    }

    //clean and load right column
    $.ajax({
        url: controlsRoot + 'arcticles-queue-list/',
        dataType : "html",
        data: {
            targetFeedId: targetFeedIdValue,
            timestamp: timestampValue
        },
        success: function (data) {
            $('div#queue').show().html(data);
        }
    });
}

var Eventlist = {
    leftcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-delete/',
            dataType : "html",
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    rightcolumn_deletepost: function(post_id, callback){callback(1)},
    leftcolumn_dropdown_change: function(sel){
        loadArticles();
    },
    rightcolumn_dropdown_change: function(sel){
        loadQueue();
    },
    calendar_change: function(timestamp){
        loadQueue();
    },
    wall_load_more: function(){
        alert('moreeee');
    },
    post_moved: function(post_id, slot_id, callback){
        window.setTimeout(function(){callback(1)},5000);
    },

    /* после выполнения запроса к сервису. Вызвать callback(state) state = {}|false */
    leftcolumn_source_edited: function(val,id, callback){callback({value: val});},
    leftcolumn_source_deleted: function(id, callback){callback(true)},
    leftcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    rightcolumn_source_edited: function(val,id, callback){callback({value: val});},
    rightcolumn_source_deleted: function(id, callback){callback(true)},
    rightcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},
    eof: null
}