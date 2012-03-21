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
            Events.fire('calendar_change', $(this).datepicker("getDate").getTime() / 1000);
        });
    $(".calendar .tip").click(function(){
        $(this).closest(".calendar").find("input").focus();
    });

    $(".drop-down").click(function(e){
        e.stopPropagation();
        $("body").click();
        var elem = $(this);
        var hidethis = function(){
            elem.removeClass("expanded");
            $("body").unbind("click", hidethis);
            elem.find("li").unbind("click", click_li);
        }
        var click_li = function(e){
            e.stopPropagation();
            elem.find(".caption")
                .removeClass("default")
                .text($(this).text());
            hidethis();
            elem.data("selected",$(this).data("id"));
            elem.trigger("change");
        }
        $("body").bind("click", hidethis);
        elem.find("li").click(click_li);
        elem.addClass("expanded");
    });

    $(".slot .post").addClass("dragged");

    (function(){
        var target = false;
        $(".post").draggable({
            revert: 'invalid',
            helper: function(){
                return $(this).clone().addClass("dragged");
            },
            stop: function(){
                target = jQuery(target);
                if(target.hasClass("empty")) {
                    if(!$(this).hasClass("dragged")) {
                        target.append($(this).addClass("dragged"));
                        target.removeClass("empty");
                    } else {
                        $(this).closest(".slot").addClass("empty");
                        target.removeClass("empty");
                    }
                    target.append($(this).addClass("dragged"));
                } else {

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
        Events.fire('leftcolumn_dropdown_change',[$(this).data("selected")]);
    });
    $(".right-panel .drop-down").change(function(){
        Events.fire('rightcolumn_dropdown_change',[$(this).data("selected")]);
    });

    $(".wall").delegate(".post .delete", "click", function(){
        var pid = $(this).closest(".post").data("id");
        $(this).closest(".post").remove();
        Events.fire('rightcolumn_deletepost',[pid]);
    });
    $(".items").delegate(".slot .post .delete", "click", function(){
        var pid = $(this).closest(".post").data("id");
        $(this).closest(".slot").addClass('empty');
        $(this).closest(".post").remove();
        Events.fire('rightcolumn_deletepost',pid);
    });

});

var Events = {};
Events.fire = function(name, args){
    if(!$.isArray(args)) args = [args];
    if($.isFunction(this[name])) this[name].apply(window, args)
}
$.extend(Events, Eventlist);
delete(Eventlist);