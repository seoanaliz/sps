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

    $(".wall").delegate(".post .delete", "click", function(){
        var elem = $(this).closest(".post"),
            pid = elem.data("id");
        Events.fire('leftcolumn_deletepost', [pid, function(state){
            if(state) {
                elem.remove();
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
        tip.click(function(){input.focus();});
        form.click(function(e){ e.stopPropagation(); });
        input.focus(function(){
            form.removeClass("collapsed");
            $(window).bind("click", stop);
        });
        var stop = function(){
            $(window).unbind("click", stop);
            if(!input.text().length && !$(".uploadifyQueueItem").length) {
                input.data("id", 0);
                form.addClass("collapsed");
            }
        }
        form.find(".save").click(function(){
            var photos = new Array();
            $('.uploadifyQueueItem').each(function(){
                var photo = new Object();
                photo.filename = $(this).find('input:hidden').val();
                photo.title = $(this).find('textarea').val();
                photos.push(photo);
            });
            form.addClass("spinner");
            Events.fire("post", [
                input.html(),
                photos,
                input.data("id"),
                function(state){
                    if(state) {
                        input.data("id", 0);
                        input.html('');
                        $('#file_upload_queue').html('');
                        stop();
                    }
                    form.removeClass("spinner");
                }
            ])
        });

        $(".left-panel").delegate(".post .edit", "click" ,function(){
            input.data("id", 0);
            input.html('');
            $('#file_upload_queue').html('');

            id = $(this).closest(".post").data("id");

            Events.fire('load_post_edit', [id, function(state, data){
                if(state && data) {
                    tip.click();
                    input.data("id", id);
                    input.html(data.text);
                    $('html, body').animate({scrollTop:0}, 'slow');
                    if(data.photos) {
                        $("#fileTemplate").tmpl( eval(data.photos), { counter: filesCounter } ).appendTo(".uploadifyQueue");
                        $("a.delete-file").bind('click', function(e) {
                            $(this).parents('div.uploadifyQueueItem').remove();
                            e.preventDefault();
                        });
                    }
                }
            }]);
        });
    })();

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
                this[name].apply(window, args)
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
        var root = $(selector);
        root.each(function(){
            var root = $(this);
            if(root.hasClass("ready")) { return }
            var imgs = root.find("img"),
                imgs_length = imgs.length;
            var wait = function(){
                if(imgs_length) { return; }
                imgs.each(function(){
                    var img = $(this).clone(),
                        div = $("<div/>");
                    $(this).replaceWith(div.append(img));
                    img.height(100);
                    div.css({width:img.width(), height:"100px", overflow:"hidden", display: "inline-block", padding:"10px"});
                    root.addClass("ready");
                });
            }
            root.find("img").load(function(){
                imgs_length--;
                wait();
            });
        });

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