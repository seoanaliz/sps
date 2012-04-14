$(document).ready(function(){
    var DD_DEFAULT_TEXT = 'Источник';

    $(".newpost").removeClass('collapsed');

    // приложить файл
    try {
        var uploader = new qq.FileUploader({
            debug: true,
            element: $('#attach-file')[0],
            action: 'upload.php',
            template: ' <div class="qq-uploader">' +
                '<ul class="qq-upload-list"></ul>' +
                '<div class="save button spr l">Отправить</div>' +
                '<a href="#" class="cancel spr l">Отменить</a>' +
                '<a href="#" class="qq-upload-button">Прикрепить</a>' +
                '</div>',
            onComplete: function(id, fileName, responseJSON) {
                var $deleteAttachLink = $('<a />', { 'href': 'javascript:;', 'text': 'удалить' });
                $deleteAttachLink.click(function(e) {
                    e.preventDefault();
                    console.log('delete attach');
                    $(this).closest('li').remove();
                });
                $('.qq-upload-list li:last-child')
                    .append($('<img />', { src: responseJSON.image }))
                    .append($deleteAttachLink);
           }
        });
    } catch (e){}

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
                        elem.before($('<div id="' + deleteMessageId + '" class="post deleted-post" data-id="' + pid + '">Сообщение удалено. <a href="javascript:;" class="recover">Восстановить.</a></div>'));
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
                var pattern = /([a-zA-Z0-9-.]+\.(?:ru|com|net|me|edu|org|info|biz|uk|ua))([a-zA-Z0-9-_?\/#,&;]+)?/im,
                    txt, matches;
                setTimeout(function() {
                    txt = input.text();
                    matches = txt.match(pattern);
                    // если приаттачили ссылку
                    if (matches && matches[0] && matches[1] && !foundLink) {
                        foundLink   = matches[0];
                        foundDomain = matches[1];

                        Events.fire("post_describe_link", [
                            foundLink,
                            function(result) {
                                if (result) {
                                    $linkDescription.empty()
                                    $linkStatus.empty()

                                    // отрисовываем ссылку
                                    if (result.img) {
                                        var $img = $('<img />', { src: result.img, alt: '', width: 75, height: 75 });
                                        $linkDescription.append($img);
                                    }
                                    if (result.title) {
                                        var $a = $('<a />', { href: foundLink, target: '_blank', text: result.title });
                                        $linkDescription.append($a);
                                    }
                                    if (result.description) {
                                        var $p = $('<p />', { text: result.description });
                                        $linkDescription.append($p);
                                    }

                                    var $span = $('<span />', { text: 'Ссылка: ' });
                                    $span.append($('<a />', { href: 'http://' + foundLink, target: '_blank', text: foundDomain }));

                                    var $deleteLink = $('<a />', { href: 'javascript:;', 'class': 'delete-link', text: 'удалить' }).click(function() {
                                        // убираем аттач ссылки
                                        $linkDescription.empty()
                                        $linkStatus.empty()
                                        $linkInfo.hide();
                                        foundLink = false;
                                        foundDomain = false;
                                    });
                                    $span.append($deleteLink);

                                    $linkStatus.html($span);

                                    $linkInfo.show();
                                }
                            }
                        ]);
                    }
                }, 10);
            })
        ;
        
        var stop = function(){
            $(window).unbind("click", stop);
            if(!input.text().length) form.addClass("collapsed");
        }
        form.find(".save").click(function(){
            form.addClass("spinner");
            Events.fire("post", [
                input.html(),
                input.data("id"),
                function(state){
                    if(state) {
                        input.data("id", 0);
                        input.html('');
                        stop();
                    }
                    form.removeClass("spinner");
                }
            ]);
        });
        form.find('.cancel').click(function(e) {
            input.text('').blur();
            form.addClass('collapsed');
            e.preventDefault();
        });
        form.find(".attach").click(
            /*TODO: attach*/
        );

        $(".left-panel").delegate(".post .edit", "click" ,function(){
            /*TODO: edit*/
            input.data("id", $(this).closest("post").data("id"));
        });
    })();

    $('.left-panel .show-cut').click(function(e) {
        var $content = $(this).closest('.content'),
            shortcut = $content.find('.shortcut').html(),
            cut      = $content.find('.cut').html();

        $content.html(shortcut + ' ' + cut);
        $(this).remove();

        e.preventDefault();
    });

    $('.right-panel .show-cut').click(function(e) {
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
    addEvents: function(){
        (function(){
            $(".slot .post").addClass("dragged");
            var target = false;
            var dragdrop = function(post, slot, callback, failback){
                Events.fire('post_moved', [post, slot, function(state){
                    (state ? callback : failback)();
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
                            dragdrop($(this).data("id"), target.data("id"), function(){
                                target.append(elem.addClass("dragged"));
                                target.removeClass("empty");
                                target.append(elem.addClass("dragged"));
                                elem.removeClass("spinner");
                            },function(){
                                elem.removeClass("spinner");
                            });
                        } else {
                            dragdrop($(this).data("id"), target.data("id"), function(){
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
            .find(".caption").text(DD_DEFAULT_TEXT).addClass("default");
    }
    $(this).trigger("change");
};