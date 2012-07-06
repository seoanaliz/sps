var articlesLoading = false;

$(function(){
    $( "#slider-range" ).slider({
        range: true,
        min: 0,
        max: 100,
        animate: 100,
        values: [50, 100],
        create: function(event, ui) {
            changeRange();
        },
        slide: function(event, ui) {
            changeRange();
        },
        change: function(event, ui) {
            changeRange();
            loadArticles(true);
        }
    });
});

function changeRange() {
    var top = $("#slider-range").slider("values", 1);
    $("#slider-range").find('a:first').html($("#slider-range").slider("values", 0));
    $("#slider-range").find('a:last').html(top == 100 ? 'TOP' : top);
}

function loadArticles(clean) {
    if (articlesLoading) return;

    articlesLoading = true;

    $('#wall-load').show();

    if (Elements.leftdd().length != 1) {
        $('.newpost').hide();
    } else {
        $('.newpost').show();
    }

    $('div#wall').append('<div style="text-align: center;" id="wall-loader"></div>');

    var from = $( "#slider-range" ).slider( "values", 0 );
    var to = $( "#slider-range" ).slider( "values", 1 );
    var sortType = $('.wall-title a').data('type');

    if ($('.type-selector a.active').data('type') == 'ads') {
        from = 0;
        to = 100;
    }

    //clean and load left column
    $.ajax({
            url: controlsRoot + 'arcticles-list/',
            dataType : "html",
            data: {
                sourceFeedIds: Elements.leftdd(),
                clean: clean,
                from : from,
                to : to,
                sortType : sortType
            }
        })
        .always(function() {
            $('#wall-load').hide();
            if (clean) {
                $('div#wall').empty();
            }
        })
        .done(function(data) {
            $('div#wall div#wall-loader').remove();
            $('div#wall').append(data);
            articlesLoading = false;
            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();
        });
}

function loadQueue() {
    if (!Elements.rightdd()) {
        return;
    }

    var type = Elements.rightType();

    if (type == 'all') {
        $('.queue-footer').hide();
    } else {
        $('.queue-footer').show();
    }

    //clean and load right column
    $.ajax({
        url: controlsRoot + 'arcticles-queue-list/',
        dataType : "html",
        data: {
            targetFeedId: Elements.rightdd(),
            timestamp: Elements.calendar(),
            type: type
        },
        success: function (data) {
            $('div#queue').show().html(data);
            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();

            $('.post.blocked').draggable('disable');
            renderQueueSize();
        }
    });
}

function renderQueueSize() {
    var size = $('div#queue div.post').length;
    $('.queue-title').text((size == 0 ? 'ничего не' : size) + ' ' + Lang.declOfNum( size, ['запланирована', 'запланировано', 'запланировано'] ));
}

function reloadArticle(id) {
    $.ajax({
        url: controlsRoot + 'arcticle-item/',
        dataType : "html",
        data: {
            id: id
        },
        success: function (data) {
            elem = $("div.post[data-id=" + id + "]");
            elem.replaceWith(data);

            Elements.addEvents();
            Elements.initImages('.post .images');
            Elements.initLinks();
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
    leftcolumn_clear_post_text: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'arcticle-clear-text/',
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
                renderQueueSize();
            }
        });
    },
    rightcolumn_save_slot: function(gridLineId, time, callback) {
        $.ajax({
            url: controlsRoot + 'grid-line-save/',
            dataType : "json",
            data: {
                startDate : null, //TODO
                endDate : null, //TODO
                time: time,
                type: Elements.rightType(),
                targetFeedId: Elements.rightdd()
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                    loadQueue();
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },
    rightcolumn_time_edit: function(gridLineId, gridLineItemId, time, callback) {
        $.ajax({
            url: controlsRoot + 'grid-line-item-save/',
            dataType : "json",
            data: {
                gridLineId: gridLineId,
                gridLineItemId: gridLineItemId,
                time: time,
                timestamp: Elements.calendar()
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                    loadQueue();
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },
    leftcolumn_dropdown_change: function(){
        loadArticles(true);
    },
    rightcolumn_dropdown_change: function(){
        var targetFeedId = Elements.rightdd();
        var sourceType = Elements.leftType();

        $('#source-select option').remove();
        $('#source-select').multiselect("refresh");

        loadQueue();

        //грузим источники для этого паблика
        $.ajax({
            url: controlsRoot + 'source-feeds-list/',
            dataType : "json",
            data: {
                targetFeedId: targetFeedId,
                type: sourceType
            },
            success: function (data) {
                for (i in data) {
                    item = data[i];
                    $('#source-select').append('<option value="' + item.sourceFeedId + '">' + item.title + '</option>');
                }

                //get data from cookie
                var cookie = $.cookie('sourceFeedIds' + targetFeedId);
                if (cookie) {
                    var selectedSources = cookie.split(',');
                    if (selectedSources) {
                        var $options = $('#source-select option');
                        for (i in selectedSources) {
                            $options.filter('[value="'+selectedSources[i]+'"]').prop('selected', true);
                        }
                    }
                }

                //slider
                $( "#slider-range" ).slider( "values", 0, 50 );
                $( "#slider-range" ).slider( "values", 1, 100 );
                cookie = $.cookie('sourceFeedRange' + targetFeedId);
                if (cookie) {
                    var ranges = cookie.split(':');
                    if (ranges.length) {
                        var from = parseInt(ranges[0]);
                        var to = parseInt(ranges[1]);

                        if (from < 0 || from > 100) {
                            from = 50;
                        }
                        if (to < 0 || to > 100 || to < from) {
                            to = 100;
                        }

                        $( "#slider-range" ).slider( "values", 0, from );
                        $( "#slider-range" ).slider( "values", 1, to );
                    }
                }

                $('#source-select').multiselect("refresh");

                if (Elements.leftdd().length == 0) {
                    $('#source-select').multiselect("checkAll");
                    $('#source-select').multiselect("refresh");
                }

                Events.fire('leftcolumn_dropdown_change', []);
            }
        });
    },
    calendar_change: function(){
        loadQueue();
    },
    rightcolumn_type_change: function(){
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
                queueId: queueId,
                type: Elements.rightType()
            },
            success: function (data) {
                if(data.success) {
                    callback(1, data.id);
                    loadQueue();
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
                    callback(true, data);
                } else {
                    callback(false, null);
                }
            }
        });
    },

    post_describe_link: function(link, callback) {
        $.ajax({
            url: controlsRoot + 'parse-url/',
            type: 'GET',
            dataType : "json",
            data: {
                url: link
            },
            success: function (data) {
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
                    if (id) {
                        //перезагружаем тело поста
                        reloadArticle(id);
                    } else {
                        //перезагружаем весь левый блок
                        loadArticles(true);
                    }

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
                    callback(data);
                } else {
                    popupError('Ошибка сохренения информации о ссылке');
                    callback(false);
                }
            }
        });
    },

    leftcolumn_sort_type_change: function() {
        loadArticles(true);
    },

    eof: null
};

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