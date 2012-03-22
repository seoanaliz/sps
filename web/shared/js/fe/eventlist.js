var timestampValue;

//init first source and target
$(document).ready(function(){
    firstSource = $(".left-panel .drop-down ul :first-child");
    firstTarget = $(".right-panel .drop-down ul :first-child");
    if (firstSource.length > 0) {
        Elements.leftdd(firstSource.data("id"));
        Events.fire('leftcolumn_dropdown_change', []);
    }
    if (firstTarget.length > 0) {
        Elements.rightdd(firstSource.data("id"));
        Events.fire('rightcolumn_dropdown_change', []);
    }
});

function loadArticles(clean) {
    if (clean) {
        $('div#wall').html('');
    }

    if (!Elements.leftdd()) {
        $('.newpost').hide();
        return;
    }

    $('.newpost').show();

    //clean and load left column
    $.ajax({
        url: controlsRoot + 'arcticles-list/',
        dataType : "html",
        data: {
            sourceFeedId: Elements.leftdd(),
            clean: clean
        },
        success: function (data) {
            $('div#wall').append(data);
            Elements.addEvents();
        }
    });
}

function loadQueue() {
    if (!Elements.rightdd()) {
        return;
    }

    //clean and load right column
    $.ajax({
        url: controlsRoot + 'arcticles-queue-list/',
        dataType : "html",
        data: {
            targetFeedId: Elements.rightdd(),
            timestamp: Elements.calendar()
        },
        success: function (data) {
            $('div#queue').show().html(data);
            Elements.addEvents();
        }
    });
}

var Eventlist = {
    leftcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-delete/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    rightcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-queue-delete/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_dropdown_change: function(){
        loadArticles(true);
    },
    rightcolumn_dropdown_change: function(){
        loadQueue();
    },
    calendar_change: function(){
        loadQueue();
    },
    wall_load_more: function(callback){
        if (!$("#wallloadmore").hasClass('hidden')) {
            loadArticles(false);
        }
        callback(true);
    },
    post_moved: function(post_id, slot_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-add-to-queue/',
            dataType : "json",
            data: {
                articleId: post_id,
                timestamp: slot_id,
                targetFeedId: Elements.rightdd()
            },
            success: function (data) {
                if(data.success) {
                    callback(1);
                } else {
                    callback(0);
                }
            }
        });
    },

    /* после выполнения запроса к сервису. Вызвать callback(state) state = {}|false */
    leftcolumn_source_edited: function(val,id, callback){callback({value: val});},
    leftcolumn_source_deleted: function(id, callback){callback(true)},
    leftcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    rightcolumn_source_edited: function(val,id, callback){callback({value: val});},
    rightcolumn_source_deleted: function(id, callback){callback(true)},
    rightcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    post: function(html, id, callback){
        // id = 0 - new post, else - edit old
        callback(false);
    },

    eof: null
}