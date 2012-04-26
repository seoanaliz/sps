$(document).ready(function(){
    $("#calendar")
        .datepicker(
        {
            dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
            monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
            firstDay: 1,
            showAnim: '',
            dateFormat: "d MM"
        }
    )
        .keydown(function(e){
            if(!(e.keyCode >= 112 && e.keyCode <= 123 || e.keyCode < 32)) e.preventDefault();
        })
        .change(function(){
            $(this).parent().find(".caption").toggleClass("default", !$(this).val().length);
            Events.fire('calendar_change', [])
        });
    $(".calendar .tip").click(function(){
        $(this).closest(".calendar").find("input").focus();
    });


    $(".drop-down").click(function(e){
        e.stopPropagation();
        $(document).click();
        var elem = $(this);
        var hidethis = function(){
            elem.removeClass("expanded");
            $(document).unbind("click", hidethis);
            elem.find("li").unbind("click", click_li);
        }
        var click_li = function(e){
            e.stopPropagation();
            elem.dd_sel($(this).data("id"));
            hidethis();
        }
        $(document).bind("click", hidethis);
        elem.find("li").click(click_li);
        elem.addClass("expanded");
    });

    $(".left-panel .drop-down").change(function(){
        Events.fire('leftcolumn_dropdown_change', []);
    });
    $(".right-panel .drop-down").change(function(){
        Events.fire('rightcolumn_dropdown_change', []);
    });

    $(".wall")
        .delegate(".post .delete", "click", function(){
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('leftcolumn_deletepost', [pid, function(state){
                if (state) {
                    var deleteMessageId = 'deleted-post-' + pid;
                    if ($('#' + deleteMessageId).length) {
                        // если уже удаляли пост, то сообщение об удалении уже в DOMе
                        $('#' + deleteMessageId).show();
                    } else {
                        // иначе добавляем
                        elem.before($('<div id="' + deleteMessageId + '" class="post deleted-post" data-id="' + pid + '">Пост удален. <a href="javascript:;" class="recover">Восстановить.</a></div>'));
                    }

                    elem.hide();
                }
            }]);
        })
        .delegate('.post .recover', 'click', function() {
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('leftcolumn_recoverpost', [pid, function(state){
                if(state) {
                    elem.hide().next().show();
                }
            }]);
        });

    $(".items").delegate(".slot .post .delete", "click", function(){
        var elem = $(this).closest(".post"),
            pid = elem.data("id");
        Events.fire('rightcolumn_deletepost', [pid, function(state){
            if(state) {
                elem.closest(".slot").addClass('empty');
                elem.closest(".slot").find('span.attach-icon').remove();
                elem.closest(".slot").find('span.hash-span').remove();
                elem.remove();
            }
        }]);
    });

    $("#wallloadmore").click(function(){
        var b = $(this);
        if(b.hasClass("disabled")) { return; }
        b.addClass("disabled");
        Events.fire('wall_load_more', function(state){
            b.removeClass("disabled");
            if(!state) {
                b.addClass("disabled");
            }
        });
    });

    (function(){
        var addInput = function(elem, defaultvalue, id){
            var input = $("<input/>");
            elem.append(input);
            input.click(function(e){e.stopPropagation();});
            input.focus();
            input.blur(function(){
                $(this).remove();
            });
            input.keydown(function(e){
                if(e.keyCode == 27) {
                    $(this).remove();
                }
                if(e.keyCode == 13) {
                    var eventname,
                        column;
                    args = [$(this).val()];
                    column = (elem.closest(".right-panel").length) ? "right" : "left";
                    if(id) {
                        args.push(id);
                        eventname = column + "column_source_edited";
                    } else {
                        eventname = column + "column_source_added"
                    }
                    args.push(function(state){
                        if(!state) return;
                        if(id) {
                            elem.find("li[data-id=" + id + "]").text(state.value);
                        } else {
                            elem.find("ul").append('<li data-id="' + state.id + '">' + state.value + '</li>');
                        }
                        elem.dd_sel(state.id || id);
                    });
                    Events.fire(eventname, args);
                    $(this).remove();
                }
            });
            if(defaultvalue) input.val(defaultvalue);
            return input;
        }
        var getDD = function(elem){
            return $(elem).closest(".header").find(".drop-down");
        }
        $(".controls .del").click(function(){
            var dd = getDD(this),
                val = dd.data("selected");
            if(!val) {return};
            var column = (dd.closest(".right-panel").length) ? "right" : "left";
            Events.fire(column + "column_source_deleted", [val, function(state){
                if(!state) { return; }
                dd.find("li[data-id=" + val + "]").remove();
                dd.dd_sel(0);
            }]);
        });
        $(".controls .gear").click(function(){
            var dd = getDD(this);
            if(!dd.data("selected")) {return};
            addInput(dd,dd.find(".caption").text(),dd.data("selected"));
        });
        $(".controls .plus").click(function(){
            addInput(getDD(this));
        });
    })();

    (function(){
        var w = $(window),
            b = $("#wallloadmore");
        w.scroll(function(){
            if(w.scrollTop() > (b.offset().top - w.outerHeight(true))) {
                b.click();
            }
        });
    })();

    (function(){
        var form = $(".newpost"),
            input = $(".input", form),
            tip = $(".tip", form);

        var $linkInfo = $('.link-info', form),
            $linkDescription = $('.link-description', $linkInfo),
            $linkStatus = $('.link-status', $linkInfo),
            foundLink, foundDomain;

        tip.click(function(){input.focus();});
        form.click(function(e){ e.stopPropagation(); });
        input
            .focus(function(){
            form.removeClass("collapsed");
            $(window).bind("click", stop);
        })
            .bind('paste', function() {
                setTimeout(function() {
                    parseUrl(input.text());
                }, 10);
            })
        ;

        var parseUrl = function(txt){
            var pattern = /([a-zA-Z0-9-.]+\.(?:ru|com|net|me|edu|org|info|biz|uk|ua))([a-zA-Z0-9-_?\/#,&;]+)?/im,
                matches;
            matches = txt.match(pattern);
            // если приаттачили ссылку
            if (matches && matches[0] && matches[1] && !foundLink) {
                foundLink   = matches[0];
                foundDomain = matches[1];

                Events.fire("post_describe_link", [
                    foundLink,
                    function(result) {
                        if (result) {
                            $linkDescription.empty();
                            $linkStatus.empty();

                            var $descriptionLayout = $('<div></div>',{'class':'post_describe_layout'});
                            $linkDescription.append($descriptionLayout);

                            // отрисовываем ссылку
                            if (result.img) {
                                var $imgBlock = $('<div></div>',{'class':'post_describe_image','title':'Редактировать картинку'}).css(
                                    {
                                        'background-image' : 'url('+result.img+')'
                                    }
                                );

                                $linkDescription.prepend($imgBlock);
                            }
                            if (result.title) {
                                var $a = $('<a />', {
                                    href: 'http://' + foundLink,
                                    target: '_blank',
                                    html: '<span>'+result.title+'</span>',
                                    title:'Редактировать заголовок'
                                });
                                var $h = $('<div></div>',{'class':'post_describe_header'});
                                $h.append($a);
                                $descriptionLayout.append($h);
                            }
                            if (result.description) {
                                var $p = $('<p />', {
                                    html: '<span>'+result.description+'</span>',
                                    title:'Редактировать описание'
                                });
                                $descriptionLayout.append($p);
                            }

                            editPostDescribeLink.load($h,$p,$imgBlock,result.imgOriginal);

                            var $span = $('<span />', { text: 'Ссылка: ' });
                            $span.append($('<a />', { href: 'http://' + foundLink, target: '_blank', text: foundDomain }));

                            var $deleteLink = $('<a />', { href: 'javascript:;', 'class': 'delete-link', text: 'удалить' }).click(function() {
                                // убираем аттач ссылки
                                deleteLink();
                            });
                            var $reloadLink = $('<a />', { href: 'javascript:;', 'class': 'reload-link', text: 'обновить', 'css' : {'display': 'none'} }).click(function() {
                                link = foundLink;
                                deleteLink();
                                parseUrl(link);
                            });
                            $span.append($deleteLink);
                            $span.append($reloadLink);

                            $linkStatus.html($span);

                            $linkInfo.show();
                        }
                    }
                ]);
            }
        }

        var editPostDescribeLink = {
            load: function ($header,$description,$image,$imageSrc) {
                this.header = $header;
                this.description = $description;
                this.image = $image;
                this.imageSrc = $imageSrc;
                this.renderEditor();
            },
            renderEditor: function() {
                var $editField = $('<input />',{type:'text',id:'post_header'});
                var $editArea = $('<textarea />',{id: 'post_description'});
                this.header.append($editField.val(this.header.text()));
                this.description.append($editArea.val(this.description.text()));

                this.bindEvts();
            },
            bindEvts: function() {
                var t = this;
                this.header.click(function() {
                    t.edit(t.header);
                    return false;
                });
                this.description.click(function() {
                    t.edit(t.description);
                    return false;
                });
                if (this.image) {
                    this.image.click(function() {
                        t.editImage(t.description);
                        return false;
                    });
                }
            },
            editImage: function() {
                this.renderEditImagePopup();
            },
            renderEditImagePopup: function() {
                var $popup = $('<div></div>',{
                    'class': 'editImagePopup',
                    'html': '<h2>Редактировать изображение</h2>'+
                        '<table><tr><td><img src="'+this.imageSrc+'" id="originalImage" /></td>'+
                        '<td><div class="previewContainer">'+
                        '<div class="previewLayout"><img id="preview" src="'+this.imageSrc+'" /></div>'+
                        '<div class="button spr save">Сохранить</div>'+
                        '<div id="attach-image-file" class="buttons attach-file">'+
                        '</div>'+
                        '</div></td></tr></table><b class="close"></b>'
                }),
                    t = this;
                $('body').append($popup);
                $('<div class="substrate"></div>').appendTo('body');
                $('#originalImage').load(function(){
                    $popup.css({
                        left: $('body').width()/2 - $popup.width()/2,
                        top: $('.link-info').position().top
                    });
                    $('.substrate').css({
                        height: $(document).height()
                    });
                });

                $popup.find('.save').click(function() {
                    t.post();
                });


                this.closeImagePopup($popup);
                this.crop();
                this.upload();
            },
            closeImagePopup: function($popup) {
                $('.substrate,.editImagePopup .close').click(function() {
                    $('.substrate').remove();
                    $popup.remove();
                });
            },
            crop: function() {
                var t = this;
                this.originalImage = $('#originalImage');
                this.previewImage = $('#preview');
                this.originalImage.load(function (){
                    t.Jcrop = $.Jcrop($(this), {
                        onChange: t.showPreview,
                        onSelect: t.showPreview,
                        aspectRatio : 2.06,
                        minSize: [130,63],
                        setSelect: [0,0,130,63]
                    });
                });
            },
            upload: function() {
                var t = this;
                try {
                    new qq.FileUploader({
                        debug: true,
                        element: $('#attach-image-file')[0],
                        action: root + 'int/controls/image-upload/',
                        template: ' <div class="qq-uploader">' +
                            '<ul class="qq-upload-list"></ul>' +
                            //'<a href="#" class="button spr qq-upload-button">Загрузить картинку</a>' +
                            '</div>',
                        onComplete: function(id, fileName, responseJSON) {
                            popupNotice('Не реализовано');
//                            $('.jcrop-holder').remove();
//                            t.originalImage.attr({src:responseJSON.image}).show();
//                            t.previewImage.attr({src:responseJSON.image});
//                            t.crop();
                        }
                    });
                } catch (e) {}
            },
            showPreview: function (coords,t) {
                var rx = $('.previewLayout').width() / coords.w;
                var ry = $('.previewLayout').height() / coords.h;

                $('#preview').css({
                    width: Math.round(rx * $('.jcrop-holder').width()) + 'px',
                    height: Math.round(ry * $('.jcrop-holder').height()) + 'px',
                    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                    marginTop: '-' + Math.round(ry * coords.y) + 'px'
                });
                editPostDescribeLink.coords = coords;
            },
            edit: function($elem) {
                var t = this;
                $elem.find('span').hide();
                $elem.find('input,textarea')
                    .css({display: 'block'})
                    .trigger('focus')
                    .unbind('blur')
                    .bind('blur',function(){
                        var $this = $(this);
                        $elem.find('span').text($this.val()).show();
                        $this.hide();
                        t.post();
                    });
            },
            post: function() {
                var t = this,
                    data = {
                        header: $('#post_header').val(),
                        description: $('#post_description').val(),
                        coords: t.coords,
                        link: $('.post_describe_header').find('a').attr('href')
                    };

                $('.editImagePopup .close').click();

                Events.fire('post_link_data', data, function(state){

                });
            }
        };

        var clearForm = function(){
            input.data("id", 0).html('');
            $('.qq-upload-list').html('');
            deleteLink();
        }

        var stop = function(){
            $(window).unbind("click", stop);

            if(!input.text().length && !$(".qq-upload-list li").length && !$linkInfo.is(":visible")) {
                input.data("id", 0);
                form.addClass("collapsed");
                deleteLink();
            }
        }

        var deleteLink = function(){
            $linkDescription.empty();
            $linkStatus.empty();
            $linkInfo.hide();
            foundLink = false;
            foundDomain = false;
        }

        form.delegate(".save", "click" ,function(e){
            var photos = new Array();
            $('.qq-upload-success').each(function(){
                var photo = new Object();
                photo.filename = $(this).find('input:hidden').val();
                photo.title = $(this).find('textarea').val();
                photos.push(photo);
            });
            form.addClass("spinner");
            Events.fire("post", [
                input.html(),
                photos,
                $linkStatus.find('a').attr('href'),
                input.data("id"),
                function(state){
                    if(state) {
                        clearForm();
                        stop();
                    }
                    form.removeClass("spinner");
                }
            ]);
        });
        form.delegate(".cancel", "click" ,function(e){
            clearForm();
            input.text('').blur();
            form.addClass('collapsed');
            e.preventDefault();
        });
        $(".left-panel").delegate(".post .edit", "click" ,function(){
            clearForm();

            id = $(this).closest(".post").data("id");

            Events.fire('load_post_edit', [id, function(state, data){
                if(state && data) {
                    tip.click();
                    input.data("id", id);
                    input.html(data.text);
                    $('html, body').animate({scrollTop:0}, 'slow');
                    if(data.photos) {
                        $("#fileTemplate").tmpl( eval(data.photos), { counter: filesCounter } ).appendTo(".qq-upload-list");
                        $(".qq-upload-success a.delete-attach").click(function(e){
                            $(this).closest('li').remove();
                            e.preventDefault();
                        });
                    }
                    if (data.link) {
                        parseUrl(data.link);
                    }
                }
            }]);
        });
    })();

    $(".left-panel").delegate(".show-cut", "click" ,function(e){
        var $content = $(this).closest('.content'),
            shortcut = $content.find('.shortcut').html(),
            cut      = $content.find('.cut').html();

        $content.html(shortcut + ' ' + cut);
        $(this).remove();

        e.preventDefault();
    });

    $(".right-panel").delegate(".show-cut", "click" ,function(e){
        var $content = $(this).closest('.content'),
            txt      = $(this).text();

        $content.find('.cut').toggle();

        $(this).text(txt == '«' ? '»' : '«');

        e.preventDefault();
    });
});

var Events = {
    fire : function(name, args){
        if(typeof args != "undefined") {
            if(!$.isArray(args)) args = [args];
        } else {
            args = [];
        }
        if($.isFunction(this[name])) {
            try {
                this[name].apply(window, args);
            } catch(e) {
                if(console && $.isFunction(console.log)) {
                    console.log(e);
                }
            }
        }
    }
}
$.extend(Events, Eventlist);
delete(Eventlist);

var Elements = {
    initImages: function(selector){
//        var root = $(selector);
//        root.each(function(){
//            var root = $(this);
//            if(root.hasClass("ready")) { return }
//            var imgs = root.find("img"),
//                imgs_length = imgs.length;
//            var wait = function(){
//                if(imgs_length) { return; }
//                imgs.each(function(){
//                    var img = $(this).clone(),
//                        div = $("<div/>");
//                    $(this).replaceWith(div.append(img));
//                    img.height(100);
//                    div.css({width:img.width(), height:"100px", overflow:"hidden", display: "inline-block", padding:"10px"});
//                    root.addClass("ready");
//                });
//            }
//            root.find("img").load(function(){
//                imgs_length--;
//                wait();
//            });
//        });

        $(".fancybox-thumb").fancybox({
            prevEffect		: 'none',
            nextEffect		: 'none',
            closeBtn		: false,
            fitToView       : false,
            helpers		: {
                title	: { type : 'inside' },
                buttons	: {}
            }
        });
    },
    addEvents: function(){
        (function(){
            $(".slot .post").addClass("dragged");
            var target = false;
            var dragdrop = function(post, slot, queueId, callback, failback){
                Events.fire('post_moved', [post, slot, queueId, function(state, newId){
                    if (state) {
                        callback(newId);
                    } else {
                        failback();
                    }
                }]);
            }
            $(".post").draggable({
                revert: 'invalid',
                cursorAt: {top: 60, left: 150},
                helper: function(){
                    return $(this).clone().addClass("dragged moving");
                },
                start: function(){
                    $(this).addClass("removed");
                },
                stop: function(){
                    $(this).removeClass("removed");
                    target = $(target);
                    var elem = $(this);
                    if(target.hasClass("empty")) {
                        if(!$(this).hasClass("dragged")) {
                            dragdrop($(this).data("id"), target.data("id"), $(this).data("queue-id"), function(newId){
                                elem.data("id", newId);
                                elem.data("queue-id", newId);
                                target.append(elem.addClass("dragged"));
                                target.removeClass("empty");
                                target.append(elem.addClass("dragged"));
                                elem.removeClass("spinner");

                                if (elem.find('.attach-icon-link').length > 0) {
                                    target.find('.time').append(' <span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>');
                                }
                                if (elem.find('.attach-icon-link-red').length > 0) {
                                    target.find('.time').append(' <span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>');
                                }
                                if (elem.find('.hash-span').length > 0) {
                                    target.find('.time').append(' <span class="hash-span" title="Пост с хештэгом">#hash</span>');
                                }
                            },function(){
                                elem.removeClass("spinner");
                            });
                        } else {
                            dragdrop($(this).data("id"), target.data("id"), $(this).data("queue-id"), function(){
                                elem.closest(".slot").addClass("empty");
                                target.removeClass("empty");
                                target.append(elem.addClass("dragged"));
                                elem.removeClass("spinner");
                            },function(){
                                elem.removeClass("spinner");
                            });
                        }
                        elem.addClass("spinner");
                    }
                }
            });

            $('.items .slot').droppable({
                activeClass: "ui-state-active",
                hoverClass: "ui-state-hover",
                drop: function(){
                    target = this;
                }
            });
        })();
    },
    leftdd: function(value){
        if(typeof value == 'undefined') {
            return $(".left-panel .drop-down").data("selected");
        } else {
            $(".left-panel .drop-down").dd_sel(value);
        }
    },
    rightdd:function(value){
        if(typeof value == 'undefined') {
            return $(".right-panel .drop-down").data("selected");
        } else {
            $(".right-panel .drop-down").dd_sel(value);
        }
    },
    calendar: function(value){
        if(typeof value == 'undefined') {
            var timestamp = $("#calendar").datepicker("getDate");
            return timestamp ? timestamp.getTime() / 1000 : null;
        } else {
            $("#calendar").datepicker("setDate", value).closest(".calendar").find(".caption").html("&nbsp;");
        }
    },
    initLinks: function(){
        var tpl = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            <div class="link-description-content">\
                <div class="link-img l" />\
                <div class="link-description-text l">\
                    <a href="" target="_blank"></a>\
                    <p></p>\
                </div>\
                <div class="clear"></div>\
            </div>';

        $('img.ajax-loader').each(function(){
            var container   = $(this).parents('div.link-info-content');
            var link        = $(this).attr('rel');
            $.ajax({
                url: controlsRoot + 'parse-url/',
                type: 'GET',
                dataType : "json",
                data: {
                    url: link
                },
                success: function (data) {
                    container.html(tpl);
                    if (data.img) {
                        container.find('.link-img').css('background-image', 'url(' + data.img + ')');
                    } else {
                        container.find('.link-img').remove();
                    }
                    if (data.title) {
                        container.find('div.link-description-text a').text(data.title);
                    }
                    if (data.description) {
                        container.find('div.link-description-text p').text(data.description);
                    }

                    container.find('a').attr('href', link);

                    var pattern = /([a-zA-Z0-9-.]+\.(?:ru|com|net|me|edu|org|info|biz|uk|ua))([a-zA-Z0-9-_?\/#,&;]+)?/im,
                        matches;
                    matches = link.match(pattern);

                    shortLink = link;
                    if (matches[1]) {
                        shortLink = matches[1];
                    }
                    container.find('div.link-status-content span a').text(shortLink);
                }
            });

        });
    }
}

$.fn.dd_sel = function(id){
    var elem = $(this);
    if(!elem.hasClass("drop-down")) return;
    if(id) {
        elem = elem.find("li[data-id=" + id + "]");
    } else {
        elem = elem.find("li:first");
    }
    if(elem.length) {
        $(this)
            .data("selected",elem.data("id"))
            .find(".caption")
            .text(elem.text())
            .removeClass("default");
    } else {
        $(this)
            .data("selected",0)
            .find(".caption").text('Источник').addClass("default");
    }
    $(this).trigger("change");
};