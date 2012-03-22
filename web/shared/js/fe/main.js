var Events = {};

$(document).ready(function(){
    var DD_DEFAULT_TEXT = 'Источник';

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
            Events.fire('calendar_change', $(this).datepicker("getDate").getTime() / 1000);
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

    $(".slot .post").addClass("dragged");

    (function(){
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
                            leftcolcheck();
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
            activeClass: "ui-state-hover",
            hoverClass: "ui-state-active",
            drop: function(){
                target = this;
            }
        });
    })();

    $(".left-panel .drop-down").change(function(){
        Events.fire('leftcolumn_dropdown_change',$(this).data("selected"));
    });
    $(".right-panel .drop-down").change(function(){
        Events.fire('rightcolumn_dropdown_change',$(this).data("selected"));
    });

    $(".wall").delegate(".post .delete", "click", function(){
        var elem = $(this).closest(".post"),
            pid = elem.data("id");
        leftcolcheck();
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
    var leftcolcheck = function(def){
        if(!def) {window.setTimeout(function(){leftcolcheck(true);},0);}
        if(!$(".wall .post").length) {
            $("#emptylabel").show();
        }
    }

    $("#wallloadmore").click(function(){
        Events.fire('wall_load_more');
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

    //init first source and target
    firstSource = $(".left-panel .drop-down ul :first-child");
    firstTarget = $(".right-panel .drop-down ul :first-child");
    if (firstSource && firstTarget) {
        $(".left-panel .drop-down").data('selected', firstSource.data("id")).change();
        $(".right-panel .drop-down").data('selected', firstTarget.data("id")).change();
    }
});

Events.fire = function(name, args){
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
$.extend(Events, Eventlist);
delete(Eventlist);