$(document).ready(function(){

});

function loadArticles(clean) {
    if (clean) {
        $('div#wall').html('');
    }

    if (Elements.leftdd().length == 0) {
        return;
    }

    if (Elements.leftdd().length != 1) {
        $('.newpost').hide();
    } else {
        $('.newpost').show();
    }


    //clean and load left column
    $.ajax({
        url: controlsRoot + 'arcticles-list/',
        dataType : "html",
        data: {
            sourceFeedIds: Elements.leftdd(),
            clean: clean
        },
        success: function (data) {
            $('div#wall').append(data);
            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();
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

            $('.post.blocked').draggable('disable');
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
    leftcolumn_recoverpost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-restore/',
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
        selectedSources = Elements.leftdd();

        $('#source-select option').remove();
        $('#source-select').multiselect("refresh");

        loadQueue();

        //грузим источники для этого паблика
        $.ajax({
            url: controlsRoot + 'source-feeds-list/',
            dataType : "json",
            data: {
                targetFeedId: Elements.rightdd()
            },
            success: function (data) {
                for (i in data) {
                    item = data[i];
                    $('#source-select').append('<option value="' + item.sourceFeedId + '">' + item.title + '</option>');
                }

                if (selectedSources) {
                    $options = $('#source-select option');
                    for (i in selectedSources) {
                        $options.filter('[value="'+selectedSources[i]+'"]').prop('selected', true);
                    }
                }

                $('#source-select').multiselect("refresh");

                if (Elements.leftdd().length == 0) {
                    $('#source-select').multiselect("checkAll");
                }

                Events.fire('leftcolumn_dropdown_change', []);
            }
        });
    },
    calendar_change: function(){
        loadQueue();
    },
    wall_load_more: function(callback){
        if (!$("#wallloadmore").hasClass('hidden')) {
            $("#wallloadmore").addClass('hidden');
            loadArticles(false);
        }
        callback(true);
    },
    post_moved: function(post_id, slot_id, queueId, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-add-to-queue/',
            dataType : "json",
            data: {
                articleId: post_id,
                timestamp: slot_id,
                targetFeedId: Elements.rightdd(),
                queueId: queueId
            },
            success: function (data) {
                if(data.success) {
                    callback(1, data.id);
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
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

    load_post_edit: function(id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-get/',
            dataType : "json",
            data: {
                articleId: id
            },
            success: function (data) {
                if(data && data.id) {
                    $('.newpost').show();
                    callback(true, data);
                } else {
                    callback(false, null);
                }
            }
        });
    },

    post_describe_link: function(link, callback) {
        $('div.link-description').html('<img src="' + root + 'shared/images/fe/ajax-loader.gif">');
        $('div.link-info').show();
        $.ajax({
            url: controlsRoot + 'parse-url/',
            type: 'GET',
            dataType : "json",
            data: {
                url: link
            },
            success: function (data) {
                $('div.link-description').html('');
                $('div.link-info').hide();
                callback(data);
            }
        });
    },

    post: function(text, photos, link, id, callback){
        $sourceFeedIds = Elements.leftdd();
        if ($sourceFeedIds.length != 1) {
            $sourceFeedId = null;
        } else {
            $sourceFeedId = $sourceFeedIds[0];
        }

        $.ajax({
            url: controlsRoot + 'arcticle-save/',
            type: 'POST',
            dataType : "json",
            data: {
                articleId: id,
                text: text,
                photos: photos,
                link: link,
                sourceFeedId: $sourceFeedId
            },
            success: function (data) {
                if(data.success) {
                    loadArticles(true);
                    callback(true);
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },

    post_link_data: function(data, callback) {
        $('div.link-description').html('<img src="' + root + 'shared/images/fe/ajax-loader.gif">');
        $.ajax({
            url: controlsRoot + 'link-info-upload/',
            type: 'GET',
            dataType : "json",
            data: {
                data: data
            },
            success: function (data) {
                if (data) {
                    $('.reload-link').click();
                } else {
                    popupError('Ошибка сохренения информации о ссылке');
                }
            }
        });
    },

    eof: null
}

function popupSuccess( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#EBF0DA url('  + root +  'shared/images/vt/ui/icon_v.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

function popupError( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#FEDADA url('  + root +  'shared/images/vt/ui/icon_x.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

function popupNotice( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#FBFFBF url('  + root +  'shared/images/vt/ui/icon_i.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}