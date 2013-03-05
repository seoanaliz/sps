Control = $.extend(Control, {
    root: controlsRoot,
    dataType: 'html',

    controlMap: {
        get_articles: {
            name: 'articles-list'
        },
        authors_get: {
            name: 'authors-list'
        },
        author_remove: {
            name: 'author-delete',
            params: {
                authorId: 'vkId'
            }
        },
        author_add: {
            name: 'author-add',
            params: {
                authorId: 'vkId'
            }
        },
        add_list: {
            name: 'add-user-group',
            dataType: 'json'
        },
        add_to_list: {
            name: 'add-user-to-group',
            params: {
                userId: 'vkId',
                listId: 'userGroupId'
            }
        },
        remove_from_list: {
            name: 'remove-user-from-group',
            params: {
                userId: 'vkId',
                listId: 'userGroupId'
            }
        }
    }
});

var Eventlist = {
    leftcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'article-delete/',
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
            url: controlsRoot + 'article-clear-text/',
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
            url: controlsRoot + 'article-restore/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_approve_post: function(post_id, callback) {
        $.ajax({
            url: controlsRoot + 'article-approved/',
            data: {
                id: post_id
            },
            success: function(data) {
                callback(1);
            }
        });
    },
    leftcolumn_reject_post: function(post_id, callback) {
        $.ajax({
            url: controlsRoot + 'article-reject/',
            data: {
                id: post_id
            },
            success: function(data) {
                callback(1);
            }
        });
    },
    rightcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'article-queue-delete/',
            data: {
                id: post_id
            },
            success: function(data) {
                app.loadQueue();
                if (typeof callback == 'function') {
                    callback(true);
                }
            }
        });
    },
    rightcolumn_save_slot: function(gridLineId, time, startDate, endDate, callback) {
        $.ajax({
            url: controlsRoot + 'grid-line-save/',
            dataType : "json",
            data: {
                gridLineId : gridLineId,
                startDate : startDate,
                endDate : endDate,
                time: time,
                type: Elements.rightType(),
                targetFeedId: Elements.rightdd()
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                    app.loadQueue();
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },
    rightcolumn_time_edit: function(gridLineId, gridLineItemId, time, qid, callback) {
        $.ajax({
            url: controlsRoot + 'grid-line-item-save/',
            dataType : "json",
            data: {
                gridLineId: gridLineId,
                gridLineItemId: gridLineItemId,
                time: time,
                timestamp: Elements.calendar(),
                queueId: qid
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                    app.loadQueue();
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },
    rightcolumn_removal_time_edit: function(gridLineId, gridLineItemId, time, qid, callback) {
        $.ajax({
            url: controlsRoot + 'plan-post-delete/',
            dataType : "json",
            data: {
                time: time,
                queueId: qid
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                    app.loadQueue();
                } else {
                    callback(false);
                }
            }
        });
    },
    leftcolumn_dropdown_change: function(){
        var targetFeedId = Elements.rightdd();
        var leftType = Elements.leftType();
        if (leftType == 'source') {
            $.cookie('sourceFeedIds' + targetFeedId, Elements.leftdd(), { expires: 7, path: '/', secure: false });
        }
        app.loadArticles(true);
    },
    rightcolumn_dropdown_change: function(){
        articlesLoading = true;

        var targetFeedId = Elements.rightdd();
        var sourceType = Elements.leftType();
        var $multiSelect = $("#source-select");
        var $leftPanel = $('.left-panel');

        //грузим источники для этого паблика
        $.ajax({
            url: controlsRoot + 'source-feeds-list/',
            dataType : "json",
            data: {
                targetFeedId: targetFeedId,
                type: sourceType
            }
        }).success(function(data) {
            var $wallSwitcher = $('#wall-switcher');
            var sourceTypes = data.accessibleSourceTypes;
            // возможно тот тип, что мы запрашивали недоступен, и нам вернули новый тип
            var $sourceTypeLink = $('#sourceType-' + data.type);
            if (!$sourceTypeLink.hasClass('active')) {
                $('.sourceType.active').removeClass('active');
                $sourceTypeLink.addClass('active');
            }
            sourceType = $sourceTypeLink.data('type');
            if (sourceType != 'source' && sourceType != 'albums') {
                $('#slider-text').hide();
                $('#slider-cont').hide();
                $('#filter-list').hide();
            } else {
                $('#slider-text').show();
                $('#slider-cont').show();
                $('#filter-list').show();
            }

            if (data.showSourceList) {
                $multiSelect.multiselect('getButton').removeClass('hidden');
            } else {
                $multiSelect.multiselect('getButton').addClass('hidden');
            }

            // фильтры по типу постов
            if (data.showArticleStatusFilter) {
                $leftPanel.find('.authors-tabs .tab').removeClass('selected');
                $leftPanel.find('.authors-tabs .tab:first').addClass('selected');
                $leftPanel.find('.authors-tabs').show();
            } else {
                $leftPanel.find('.authors-tabs').hide();
            }

            // группы юзеров
            $wallSwitcher.hide();
            var $userGroupTabs = $('.user-groups-tabs');
            if (sourceType == 'authors') {
                if (data.authorsFilters && (data.authorsFilters.all_my_filter || data.authorsFilters.article_status_filter)) {
                    var showSwitcherType;
                    if (data.authorsFilters.all_my_filter) {
                        showSwitcherType = 'all';
                    } else {
                        showSwitcherType = 'deferred';
                    }
                    $wallSwitcher.show();
                    $wallSwitcher.find('a').hide();
                    $wallSwitcher.find('a[data-type="' + showSwitcherType + '"]').show();
                }

                var userGroups = data.showUserGroups;
                $userGroupTabs.empty();
                $userGroupTabs.removeClass('hidden');
                $userGroupTabs.append('<div class="tab selected">Все новости</div>');
                if (userGroups) {
                    for (var i in userGroups) {
                        var userGroupModel = new UserGroupModel();
                        userGroupModel.id(userGroups[i]['id']);
                        userGroupModel.name(userGroups[i]['name']);
                        userGroupCollection.add(userGroupModel.id(), userGroupModel);
                        $userGroupTabs.append('<div class="tab" data-user-group-id="' + userGroups[i]['id'] + '">' + userGroups[i]['name'] + '</div>');
                    }
                }
            } else {
                $userGroupTabs.addClass('hidden');
            }

            var $typeSelector = $('.left-panel div.type-selector');
            $typeSelector.children('.sourceType').each(function(i, item) {
                item = $(item);
                if ($.inArray(item.data('type'), sourceTypes) == -1) {
                    item.hide();
                } else {
                    item.show();
                }
            });

            $.cookie('sourceTypes' + targetFeedId, sourceType);

            //init slider
            app.initSlider(targetFeedId, sourceType);

            $('#source-select option').remove();

            for (var i in data.sourceFeeds) {
                var item = data.sourceFeeds[i];
                $multiSelect.append('<option value="' + item.id + '">' + item.title + '</option>');
            }

            $('.left-panel div.type-selector').children('.sourceType').each(function(i, item){
                item = $(item);
                if ($.inArray(item.data('type'), sourceTypes) == -1){
                    item.hide();
                } else {
                    item.show();
                }
            });

            var gridTypes = data.accessibleGridTypes;
            var showCount = 0;
            $('#right-panel .type-selector').children('.grid_type').each(function(i, item){
                item = $(item);
                if ($.inArray(item.data('type'), gridTypes) == -1){
                    item.hide();
                } else {
                    showCount++;
                    item.show();
                }
            });
            if (showCount > 2) {
                $('a.grid_type.all').show();
            } else {
                $('a.grid_type.all').hide();
            }

            var addCellButton = $('div.queue-footer > a.add-button');
            if (data['canAddPlanCell']) {
                addCellButton.show();
            } else {
                addCellButton.hide();
            }

            app.loadQueue();

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

            $multiSelect.multiselect("refresh");
            if (Elements.leftdd().length == 0) {
                $multiSelect.multiselect("checkAll").multiselect("refresh");
            }

            articlesLoading = false;
            Events.fire('leftcolumn_dropdown_change');
        });
    },
    calendar_change: function(){
        app.loadQueue();
    },
    rightcolumn_type_change: function(){
        $.cookie('targetTypes' + Elements.rightdd(), Elements.rightType());
        app.loadQueue();
    },
    wall_load_more: function(callback){
        app.loadArticles(false);
        callback(true);
    },
    post_moved: function(post_id, slot_id, queueId, callback){
        $.ajax({
            url: controlsRoot + 'article-add-to-queue/',
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
                    app.loadQueue();
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(0);
                }
            }
        });
    },

    load_post_edit: function(id, callback){
        $.ajax({
            url: controlsRoot + 'article-get/',
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
        var $sourceFeedIds = Elements.leftdd();
        var $sourceFeedId;
        if ($sourceFeedIds.length != 1) {
            $sourceFeedId = null;
        } else {
            $sourceFeedId = $sourceFeedIds[0];
        }

        $.ajax({
            url: controlsRoot + 'article-save/',
            type: 'POST',
            dataType : "json",
            data: {
                articleId: id,
                text: text,
                photos: photos,
                link: link,
                sourceFeedId: $sourceFeedId,
                targetFeedId: Elements.rightdd(),
                userGroupId: Elements.getUserGroupId()
            },
            success: function (data) {
                if(data.success) {
                    if (id) {
                        //перезагружаем тело поста
                        app.reloadArticle(id);
                    } else {
                        //перезагружаем весь левый блок
                        app.loadArticles(true);
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

    add_article_group: function(targetFeedId, name, callback) {
        $.ajax({
            url: controlsRoot + 'add-user-group/',
            type: 'POST',
            dataType : "json",
            data: {
                name: name,
                targetFeedId: targetFeedId
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                } else {
                    callback(false);
                }
            }
        });
    },


    leftcolumn_sort_type_change: function() {
        app.loadArticles(true);
    },

    comment_load: function(options, callback) {
        var params = $.extend({
            postId: null,
            all: true
        }, options);

        $.ajax({
            url: appControlsRoot + 'comments-load/',
            data: params,
            success: function (data) {
                callback(data);
            }
        });
    },
    comment_post: function(postId, text, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-save/',
            type: 'POST',
            data: {
                id: postId,
                text: text
            },
            success: function (data) {
                callback(data);
            }
        });
    },
    comment_delete: function(commentId, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-delete/',
            data: {
                id: commentId
            },
            success: function (data) {
                callback(true);
            }
        });
    },
    comment_restore: function(commentId, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-restore/',
            data: {
                id: commentId
            },
            success: function (data) {
                callback(true);
            }
        });
    },

    get_author_articles: function(articleStatus, callback) {
        var slider = $( "#slider-range" );
        var from = slider.slider( "values", 0 );
        var to = slider.slider( "values", 1 );

        Elements.getWallLoader().show();
        var requestData =  {
            sourceFeedIds: Elements.leftdd(),
            page: wallPage,
            from: from,
            to: to,
            sortType: Elements.getSortType(),
            type: Elements.leftType(),
            targetFeedId: Elements.rightdd()
        };
        if (typeof articleStatus != 'undefined'){
            requestData['articleStatus'] = articleStatus;
        }

        $.ajax({
            url: controlsRoot + 'articles-list/',
            dataType : "html",
            data: requestData
        }).always(function() {
            Elements.getWallLoader().hide();
        }).done(callback);
    },

    eof: null
};

var Events = {
    delay: 0,
    isDebug: false,
    eventList: Eventlist,
    fire: function(name){
        var t = this;
        var args;
        if (arguments.length == 2 && (typeof arguments[1] == 'object') && arguments[1].length) {
            args = arguments[1];
        } else {
            args = Array.prototype.slice.call(arguments, 1);
        }
        if ($.isFunction(t.eventList[name])) {
            try {
                setTimeout(function() {
                    if (window.console && console.log && t.isDebug) {
                        console.groupCollapsed(name);
                        console.log('args: ' + args.slice(0, -1));
                        console.groupEnd(name);
                    }
                    t.eventList[name].apply(window, args);
                }, t.delay);
            } catch(e) {
                if (window.console && console.log && t.isDebug) {
                    console.groupCollapsed('Error');
                    console.log(e);
                    console.groupEnd('Error');
                }
            }
        }
    }
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
