var pattern = /\b(https?|ftp):\/\/([\-A-Z0-9.]+)(\/[\-A-Z0-9+&@#\/%=~_|!:,.;]*)?(\?[A-Z0-9+&@#\/%=~_|!:,.;]*)?/im;

$(document).ready(function(){
    // Календарь
    $("#calendar")
        .datepicker (
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
            Events.fire('calendar_change', []);
        });
    $(".calendar .tip").click(function(){
        $(this).closest(".calendar").find("input").focus();
    });

    // Приведение вида календаря из 22.12.2012 в 22 декабря
    (function() {
        var d = $("#calendar").val().split('.');
        var i = d[1];
        d[1] = d[0];
        d[0] = i;
        var date = d.join('.');
        $("#calendar").datepicker('setDate', new Date(date)).trigger('change');
    })();

    // Кнопки вперед-назад в календаре
    (function() {
        var day = (86000 * 1000);

        $(".calendar .prev").click(function(){
            var date = $('#calendar').datepicker('getDate').getTime();
            $("#calendar").datepicker('setDate', new Date(date - day)).trigger('change');
        });
        $(".calendar .next").click(function(){
            var date = $('#calendar').datepicker('getDate').getTime();
            $("#calendar").datepicker('setDate', new Date(date + day * 2)).trigger('change');
        });
    })();

    // Left menu multiselect
    $("#source-select").multiselect({
        minWidth: 250,
        height: 250,
        checkAllText: 'Выделить все',
        uncheckAllText: 'Сбросить',
        noneSelectedText: '<span class="gray">Источник не выбран</span>',
        selectedText: '<span class="counter">#</span> выбрано',
        checkAll: function(){
            Events.fire('leftcolumn_dropdown_change', []);
        },
        uncheckAll: function(){
            Events.fire('leftcolumn_dropdown_change', []);
        }
    });
    $("#source-select").bind("multiselectclick", function(event, ui){
        Events.fire('leftcolumn_dropdown_change', []);
    });

    //Dropdowns
//    $(".drop-down").click(function(e){
//        e.stopPropagation();
//        $(document).click();
//        var elem = $(this);
//        var hidethis = function(){
//            elem.removeClass("expanded");
//            $(document).unbind("click", hidethis);
//            elem.find("li").unbind("click", click_li);
//        };
//        var click_li = function(e){
//            e.stopPropagation();
//            elem.dd_sel($(this).data("id"));
//            hidethis();
//        };
//        $(document).bind("click", hidethis);
//        elem.find("li").click(click_li);
//        elem.addClass("expanded");
//    });

    $(".left-panel .drop-down").change(function(){
        Events.fire('leftcolumn_dropdown_change', []);
    });

    $('.wall-title a').dropdown({
        width: 'auto',
        addClass: 'wall-title-menu',
        position: 'right',
        data: [
            {title: 'новые записи', type : 'new'},
            {title: 'старые записи', type : 'old'},
            {title: 'лучшие записи', type : 'best'}
        ],
        oncreate: function() {

        },
        onopen: function() {
        },
        onclose: function() {
        },
        onchange: function(item) {
            $('.wall-title a').text(item.title).data('type', item.type);
            Events.fire('leftcolumn_sort_type_change', []);
        }
    });

    // Вкладки "Источники", "Реклама" в левон меню
    $(".left-panel .type-selector a").click(function(e){
        e.preventDefault();

        $(".left-panel .type-selector a").removeClass('active');
        $(this).addClass('active');

        if ($(this).data('type') == 'ads') {
            $('#slider-text').hide();
            $('#slider-cont').hide();
        } else {
            $('#slider-text').show();
            $('#slider-cont').show();
        }

        Events.fire('rightcolumn_dropdown_change', []);
    });

    // Wall init
    $(".wall")
        .delegate(".post .delete", "click", function(){
            var elem = $(this).closest(".post"),
                pid = elem.data("id"),
                gid = elem.data('group');
            Events.fire('leftcolumn_deletepost', [pid, function(state){
                if (state) {
                    var deleteMessageId = 'deleted-post-' + pid;
                    if ($('#' + deleteMessageId).length) {
                        // если уже удаляли пост, то сообщение об удалении уже в DOMе
                        $('#' + deleteMessageId).show();
                    } else {
                        // иначе добавляем
                        elem.before($(
                            '<div id="' + deleteMessageId + '" class="bb post deleted-post" data-group="' + gid + '" data-id="' + pid + '">' +
                                'Пост удален. <a href="javascript:;" class="recover">Восстановить</a><br/>' +
                                '<span class="button ignore">Не показывать новости сообщества</span>' +
                            '</div>'
                        ));
                    }

                    elem.hide();
                }
            }]);
        })
        .delegate('.post .ignore', 'click', function() {
            var elem = $(this).closest(".post"),
                gid = elem.data("group");
            var $menu = $("#source-select").multiselect('widget');
            $menu.find('[value=' + gid + ']:checkbox').each(function() {
                this.click();
            });
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

    // Удание постов правом меню
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

    // Загрузка стены по клику
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

    // Очистка текста
    $(".left-panel").delegate(".clear-text", "click", function(){
        var id = $(this).closest(".post").data("id");
        var post = $(this).closest(".post");

        if (confirm("Вы уверены, что хотите очистить текст записи?") ) {
            Events.fire('leftcolumn_clear_post_text', [id, function(state){
                if(state) {
                    post.find('div.shortcut').html('');
                    post.find('div.cut').html('');
                    post.find('a.show-cut').remove();
                }
            }]);
        }
    });

    // Устарело?
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
        };
        var getDD = function(elem){
            return $(elem).closest(".header").find(".drop-down");
        };
        $(".controls .del").click(function(){
            var dd = getDD(this),
                val = dd.data("selected");
            if(!val) {return}
            var column = (dd.closest(".right-panel").length) ? "right" : "left";
            Events.fire(column + "column_source_deleted", [val, function(state){
                if(!state) { return; }
                dd.find("li[data-id=" + val + "]").remove();
                dd.dd_sel(0);
            }]);
        });
        $(".controls .gear").click(function(){
            var dd = getDD(this);
            if(!dd.data("selected")) {return}
            addInput(dd,dd.find(".caption").text(),dd.data("selected"));
        });
        $(".controls .plus").click(function(){
            addInput(getDD(this));
        });
    })();

    // Автоподгрузка записей
    (function(){
        var w = $(window),
            b = $("#wallloadmore");
        w.scroll(function(){
            if(w.scrollTop() > (b.offset().top - w.outerHeight(true) - w.height())) {
                b.click();
            }
        });
    })();

    // Добавление записи в борд
    (function(){
        var form = $(".newpost"),
            input = $("textarea", form),
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
                    parseUrl(input.val());
                }, 10);
            })
            .autoResize()
            .keyup(function (e) {
                if (e.ctrlKey && e.keyCode == 13) {
                    form.find('.save').click();
                }
            }).keyup()
        ;

        var parseUrl = function(txt){
            var matches = txt.match(pattern);

            // если приаттачили ссылку
            if (matches && matches[0] && matches[1] && !foundLink) {
                foundLink   = matches[0];
                foundDomain = matches[2];

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
                                    href: foundLink,
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
                            $span.append($('<a />', { href: foundLink, target: '_blank', text: foundDomain }));

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
        };

        // Редактирование ссылки
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
                if (this.header) {
                    this.header.append($editField.val(this.header.text()));
                }
                if (this.description) {
                    this.description.append($editArea.val(this.description.text()));
                }

                this.bindEvts();
            },
            bindEvts: function() {
                var t = this;
                if (this.header) {
                    this.header.click(function() {
                        t.edit(t.header);
                        return false;
                    });
                }
                if (this.description) {
                    this.description.click(function() {
                        t.edit(t.description);
                        return false;
                    });
                }
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
            input.data("id", 0).val('');
            $('.qq-upload-list').html('');
            deleteLink();
        };

        var stop = function(){
            $(window).unbind("click", stop);

            if(!input.val().length && !$(".qq-upload-list li").length && !$linkInfo.is(":visible")) {
                input.data("id", 0);
                form.addClass("collapsed");
                deleteLink();
            }
        };

        var deleteLink = function(){
            $linkDescription.empty();
            $linkStatus.empty();
            $linkInfo.hide();
            foundLink = false;
            foundDomain = false;
        };

        form.delegate(".save", "click" ,function(e){
            var link = $linkStatus.find('a').attr('href');
            var photos = new Array();
            $('.newpost .qq-upload-success').each(function(){
                var photo = new Object();
                photo.filename = $(this).find('input:hidden').val();
                photo.title = $(this).find('textarea').val();
                photos.push(photo);
            });
            if (!($.trim(input.val() || photos.length || link))) {
                return input.focus();
            } else {
                form.addClass("spinner");
                Events.fire("post", [
                    input.val(),
                    photos,
                    link,
                    input.data("id"),
                    function(state){
                        if(state) {
                            clearForm();
                            stop();
                        }
                        form.removeClass("spinner");
                        input.blur();
                    }
                ]);
            }
        });
        form.delegate(".cancel", "click" ,function(e){
            clearForm();
            input.val('').blur();
            form.addClass('collapsed');
            e.preventDefault();
        });
        form.delegate(".image-attach", "click" ,function(e){
            input.focus();
            $('.newpost .qq-upload-button').trigger('focus');
        });

        // Редактирование поста в левом меню
        $(".left-panel").delegate(".post .edit", "click", function(){

            var $post = $(this).closest(".post"),
                $content = $post.find('.content'),
                $buttonPanel = $post.find('.bottom.d-hide'),
                postId = $post.data("id");

            if ($post.editing) return;

            Events.fire('load_post_edit', [postId, function(state, data){
                if (state && data) {

                    (function($post, $el, data) {

                        function setSelectionRange(input, selectionStart, selectionEnd) {
                            if (input.setSelectionRange) {
                                input.focus();
                                input.setSelectionRange(selectionStart, selectionEnd);
                            }
                            else if (input.createTextRange) {
                                var range = input.createTextRange();
                                range.collapse(true);
                                range.moveEnd('character', selectionEnd);
                                range.moveStart('character', selectionStart);
                                range.select();
                            }
                        }
                        function setCaretToPos (input, pos) {
                            setSelectionRange(input, pos, pos);
                        }

                        function parseUrl(txt, callback) {
                            var matches = txt.match(pattern);
                            if (matches && matches[0] && matches[1]) {
                                var foundLink = matches[0];
                                var foundDomain = matches[2];
                                if ($.isFunction(callback)) callback(foundLink, foundDomain);
                            }
                        }
                        function addLink(link, domain, el) {
                            Events.fire("post_describe_link", [
                                link,
                                function(data) {
                                    var savePost = function(d) {
                                        d = d || {};
                                        Events.fire('post_link_data', [
                                            {
                                                link: d.link || link,
                                                header: d.title || data.title,
                                                coords: d.coords || data.coords,
                                                description: d.description || data.description
                                            }, function(data) {
                                                if (data) {
                                                    if (data.img) {
                                                        el.find('.link-img').css('background-image', 'url(' + data.img + ')');
                                                    }
                                                    popupSuccess('Изменения сохранены');
                                                }
                                            }
                                        ]);
                                    };
                                    var $del = $('<div/>', {class: 'delete-attach'}).click(function() {
                                        $links.html('');
                                    });
                                    el.html(linkTplFull);
                                    el.find('a').attr('href', link).html(domain);
                                    el.find('.link-status-content').append($del);

                                    if (data.img) {
                                        el.find('.link-img')
                                            .css('background-image', 'url(' + data.img + ')')
                                            .click(function() {
                                                var originalImage = new Image();
                                                originalImage.src = data.imgOriginal;
                                                originalImage.onload = function () {
                                                    var linkImageCoords = {};
                                                    var closePopup = function() {
                                                        $popup.remove();
                                                        $bg.remove();
                                                    };
                                                    var showPreview = function(coords)
                                                    {
                                                        linkImageCoords = coords;
                                                        var $preview = $popup.find('.preview');
                                                        var rx = $preview.width() / coords.w;
                                                        var ry = $preview.height() / coords.h;

                                                        $preview.find('> img').css({
                                                            width: Math.round(rx * $('.jcrop-holder').width()) + 'px',
                                                            height: Math.round(ry * $('.jcrop-holder').height()) + 'px',
                                                            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                                                            marginTop: '-' + Math.round(ry * coords.y) + 'px'
                                                        });
                                                    };
                                                    var $bg = $('<div/>', {class: 'popup-bg'}).appendTo('body');
                                                    var $popup = $('<div/>', {
                                                            'class': 'popup-image-edit',
                                                            'html': '<div class="title">Редактировать изображение</div>'+
                                                                '<div class="close"></div>' +
                                                                '<div class="left-column">' +
                                                                    '<div class="original"><img src="'+originalImage.src+'" /></div>' +
                                                                '</div>' +
                                                                '<div class="right-column">' +
                                                                    '<div class="preview"><img src="'+originalImage.src+'" /></div>'+
                                                                    '<div class="button spr save">Сохранить</div>'+
                                                                '</div>'
                                                        })
                                                        .appendTo('body');

                                                    $bg.click(closePopup);
                                                    $popup.css({'margin-left': -$popup.width()/2});
                                                    $popup.find('.close').click(closePopup);
                                                    $popup.find('.save').click(function() {
                                                        data.coords = linkImageCoords;
                                                        savePost({coords: linkImageCoords});
                                                        closePopup();
                                                    });
                                                    $popup.find('.original > img').Jcrop({
                                                        onChange: showPreview,
                                                        onSelect: showPreview,
                                                        aspectRatio: 2.06,
                                                        minSize: [130,63],
                                                        setSelect: [0,0,130,63]
                                                    });
                                                };
                                            });
                                    } else {
                                        el.find('.link-img').remove();
                                    }
                                    if (data.title) {
                                        el.find('div.link-description-text a')
                                            .text(data.title)
                                            .click(function() {
                                                var $title = $(this);
                                                $title.attr('contenteditable', true).focus();
                                                return false;
                                            })
                                            .blur(function() {
                                                var $title = $(this);
                                                $title.attr('contenteditable', false);
                                                data.title = $title.text();
                                                savePost({title: $title.text()});
                                            });
                                    }
                                    if (data.description) {
                                        el.find('div.link-description-text p')
                                            .text(data.description)
                                            .click(function() {
                                                var $description = $(this);
                                                $description.attr('contenteditable', true).focus();
                                                return false;
                                            })
                                            .blur(function() {
                                                var $description = $(this);
                                                $description.attr('contenteditable', false);
                                                data.description = $description.text();
                                                savePost({description: $description.text()});
                                            });
                                    }
                                }
                            ]);
                        }
                        function addPhoto(path, filename, el) {
                            var $photo = $('<span/>', {class: 'attachment'})
                                .append('<img src="' + path + '" alt="" />')
                                .append($('<div />', {class: 'delete-attach', title: 'Удалить'})
                                .click(function() {
                                        $photo.remove();
                                    })
                                )
                                .append($('<input />', {type: 'hidden', name: '', value: filename}))
                                .appendTo(el);
                        }

                        var cache = {
                            html: $el.html(),
                            scroll: $(window).scrollTop()
                        };
                        $post.find('> .content').draggable('disable');
                        $post.editing = true;
                        $buttonPanel.hide();
                        $el.html('');

                        var $edit = $('<div/>', {class: 'editing'}).appendTo($el);
                        var $content = $('<div/>').appendTo($edit);
                        var $attachments = $('<div/>', {class: 'attachments'}).appendTo($edit);
                        var $text = $('<textarea/>').appendTo($content);
                        var $links = $('<div/>', {class: 'links link-info-content'}).appendTo($attachments);
                        var $photos = $('<div/>', {class: 'photos'}).appendTo($attachments);
                        var $actions = $('<div/>', {class: 'actions'}).appendTo($edit);
                        var $saveBtn = $('<div/>', {class: 'save button spr l', html: 'Сохранить'}).click(function() {onSave()}).appendTo($actions);
                        var $cancelBtn = $('<a/>', {class: 'cancel l', html: 'Отменить'}).click(function() {onCancel()}).appendTo($actions);
                        var $uploadBtn = $('<a/>', {class: 'upload r', html: 'Прикрепить'}).appendTo($actions);

                        var uploader = new qq.FileUploader({
                            debug: true,
                            element: $uploadBtn.get(0),
                            action: root + 'int/controls/image-upload/',
                            template: '<div class="qq-uploader">' +
                                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                                '<div class="qq-upload-button">Прикрепить</div>' +
                                '<ul class="qq-upload-list"></ul>' +
                                '</div>',
                            onComplete: function(id, fileName, res) {
                                addPhoto(res.image, res.filename, $photos);
                            }
                        });
                        var onSave = function() {
                            var text = $text.val();
                            var link = $links.find('a').attr('href');
                            var photos = new Array();
                            $photos.children().each(function() {
                                var photo = new Object();
                                photo.filename = $(this).find('input:hidden').val();
                                photos.push(photo);
                            });
                            if (!($.trim(text) || link || photos.length)) {
                                return $text.focus();
                            } else {
                                Events.fire("post", [
                                    text,
                                    photos,
                                    link,
                                    postId,
                                    function(data) {}
                                ]);
                            }
                        };
                        var onCancel = function() {
                            $post.find('> .content').draggable('enable');
                            $post.editing = false;
                            $buttonPanel.show();
                            $el.html(cache.html);
                            $edit.remove();
                        };

                        if (true || data.text) {
                            var text = data.text;
                            $text
                                .val(text.split('<br />').join('')) // because it's textarea
                                .appendTo($content)
                                .bind('paste', function(e) {
                                    setTimeout(function() {
                                        parseUrl($text.val(), function(link, domain) {
                                            if ($text.link && $links.html() || $text.link == link) return;
                                            $text.link = link;
                                            addLink(link, domain, $links);
                                        });
                                    }, 0);
                                })
                                .bind('keyup', function(e) {
                                    if (e.ctrlKey && e.keyCode == 13) {
                                        onSave();
                                    }
                                })
                                .autoResize()
                                .keyup().focus();
                            setCaretToPos($text.get(0), text.length);
                        }

                        if (data.link) {
                            var link = data.link;
                            parseUrl(data.link, function(link, domain) {
                                addLink(link, domain, $links);
                            });
                        }

                        if (data.photos) {
                            var photos = eval(data.photos);
                            $(photos).each(function() {
                                addPhoto(this.path, this.filename, $photos);
                            });
                        }
                    })($post, $content, data);
                }
            }]);
        });
    })();

    // Показать полностью в левом меню
    $(".left-panel").delegate(".show-cut", "click" ,function(e){
        var $content = $(this).closest('.content'),
            $shortcut = $content.find('.shortcut'),
            shortcut = $shortcut.html(),
            cut      = $content.find('.cut').html();

        $shortcut.html(shortcut + ' ' + cut);
        $(this).remove();

        e.preventDefault();
    });

    // Показать полностью в правом меню
    $(".right-panel").delegate(".toggle-text", "click", function(e) {
        $(this).parent().toggleClass('collapsed');
    });

    // Кнопка "наверх"
    (function(w) {
        var $elem = $('#go-to-top');
        $elem.click(function() {
            $(w).scrollTop(0);
        });
        $(w).bind('scroll', function(e) {
            if (e.currentTarget.scrollY <= 0) {
                $elem.hide();
            } else if (!$elem.is(':visible')) {
                $elem.show();
            }
        });
    })(window);

    // Подгрузка имени и аватара
    (function() {
        VK.init({
            apiId: vk_appId,
            nameTransportPath: '/xd_receiver.htm'
        });
        getInitData();

        function getInitData() {
            var code;
            code = 'return {';
            code += 'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]';
            code += '};';
            VK.Api.call('execute', {'code': code}, onGetInitData);
        }
        function onGetInitData(data) {
            var r;
            if (data.response) {
                r = data.response;
                if (r.me) {
                    console.log(r.me);
                    var $userInfo = $('.user-info');
                    $('.user-name a', $userInfo).text(r.me.first_name + ' ' + r.me.last_name);
                    $('a', $userInfo).attr('href', 'http://vk.com/id' + r.me.uid);
                    $('.user-photo img', $userInfo).attr('src', r.me.photo);
                }
            }
        }
    })();

    // ===
    Elements.addEvents();
});

var linkTplFull = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            <div class="link-description-content">\
                <div class="link-img l" />\
                <div class="link-description-text l">\
                    <a href="" target="_blank"></a>\
                    <p></p>\
                </div>\
                <div class="clear"></div>\
            </div>';

var linkTplShort = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            </div>';

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
};

// Image composition
(function($) {
    $.fn.imageComposition = function(hardPosition) {
        return this.each(function() {
            var CLASS_LOADING = 'image-compositing';
            var VER = 'ver', HOR = 'hor';
            hardPosition == 'right' ? VER : (hardPosition == 'bottom' ? HOR : false);

            var position = hardPosition;
            var $wrap = $(this);
            var $images = $wrap.find('img');
            var num = $images.length;
            var imagesPerColumn = 5;
            var wrap = {
                el: $wrap,
                width: 0,
                height: 0,
                maxWidth: $wrap.width(),
                maxHeight: $wrap.height(),
                type: ''
            };

            $wrap.addClass(CLASS_LOADING);
            $images.each(function(i, image) {
                var $img = $(image);

                var img = new Image();
                img.src = $img.attr('src');
                img.onload = function() {
                    if (i == num - 1) {
                        return onLoadImages();
                    }
                };
            });

            // ======================== //
            function isHor($image) {
                var w = $image.width();
                var h = $image.height();
                return !!(w / h > 1.1);
            }

            function isVer($image) {
                return !isHor($image);
            }

            function relativeResize(size, type, width) {
                var w = (type == 'width') ? 'width' : 'height';
                var h = (type == 'height') ? 'width' : 'height';
                var coef = size[w] / size[h];

                size[w] = width;
                size[h] = size[w] / coef;

                return size;
            }

            function onLoadImages() {
                var $firstImage;
                var columns = [];

                if ((num - 1) % imagesPerColumn == 1) {
                    imagesPerColumn++;
                } else if ((num - 1) % imagesPerColumn == 2) {
                    imagesPerColumn--;
                }
                if (num == 2 && !hardPosition) {
                    position = VER;
                    wrap.width = wrap.maxWidth;
                    $firstImage = $wrap;
                }

                $images.each(function(i) {
                    var $image = $(this);

                    if (i == 0) {
                        $firstImage = $image;
                        if (!position && isHor($firstImage)) {
                            position = HOR;
                            $firstImage.width(Math.min(wrap.maxWidth, $firstImage.width()));
                            wrap.width = $firstImage.width();
                            $wrap.width(wrap.width);
                        } else {
                            position = VER;
                            $firstImage.height(Math.min(wrap.maxHeight, $firstImage.height()));
                            wrap.height = $firstImage.height();
                            $wrap.height(wrap.height);
                        }
                    } else {
                        var columnIndex = Math.floor((i - 1) / (position == HOR ? imagesPerColumn : 99));
                        if (!columns[columnIndex]) columns[columnIndex] = {};

                        var column = columns[columnIndex];
                        if (!column.images) column.images = [];
                        if (!column.columnHeight) column.columnHeight = 99999;
                        if (position == HOR) {
                            column.columnHeight = Math.min(column.columnHeight, $image.height());
                        } else {
                            column.columnHeight = Math.min(column.columnHeight, $image.width());
                        }
                        column.images.push($image);
                    }
                });

                $(columns).each(function(columnIndex, column) {
                    $(column.images).each(function(imageIndex, $image) {
                        if (!column.columnWidth) column.columnWidth = 0;
                        if (position == HOR) {
                            $image.height(column.columnHeight);
                            column.columnWidth += $image.width();
                        } else {
                            $image.width(column.columnHeight);
                            column.columnWidth += $image.height();
                        }
                    });
                });

                var columnsHeight = 0;
                $(columns).each(function(columnIndex, column) {
                    var coef = 0;
                    var columnHeight = 0;

                    if (position == HOR) {
                        coef = wrap.width / column.columnWidth;
                    } else {
                        coef = wrap.height / column.columnWidth;
                    }

                    $(column.images).each(function(imageIndex, $image) {
                        var s = {};
                        if (position == HOR) {
                            s = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'width', ($image.width() * coef));

                            $image.width(s['width']);
                            $image.height(s['height']);
                            columnHeight = s['height'];
                        } else {
                            s = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'height', ($image.height() * coef));

                            $image.width(s['width']);
                            $image.height(s['height']);
                            columnHeight = s['width'];
                        }
                    });
                    columnsHeight += columnHeight;
                });

                var coef;
                if (position == HOR) {
                    wrap.height = $firstImage.height() + columnsHeight;

                    if (wrap.height > wrap.maxHeight) {
                        coef = wrap.maxHeight / wrap.height;

                        $images.each(function(i) {
                            var $image = $(this);
                            var size = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'height', ($image.height() * coef));
                            $image.width(size['width']);
                            $image.height(size['height']);
                        });
                        wrap.width *= coef;
                        wrap.height *= coef;
                    }
                } else {
                    wrap.width = $firstImage.width() + columnsHeight;

                    if (wrap.width > wrap.maxWidth) {
                        coef = wrap.maxWidth / wrap.width;

                        $images.each(function(i) {
                            var $image = $(this);
                            var size = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'width', ($image.width() * coef));
                            $image.width(size['width']);
                            $image.height(size['height']);
                        });
                        wrap.width *= coef;
                        wrap.height *= coef;
                    }
                }

                $wrap.width(wrap.width);
                $wrap.height(wrap.height);
                $wrap.removeClass(CLASS_LOADING);
            }
        });
    };
})(jQuery);

// Автовысота у textarea
(function($) {
    $.fn.autoResize = function() {
        return this.each(function() {
            var $input = $(this);
            if (!$input._autoResize) {
                $input
                    ._autoResize = $('<div/>')
                    .appendTo('body')
                    .css({
                        width: $input.width(),
                        minHeight: $input.height() - ($input.css('padding-top') + $input.css('padding-bottom')),
                        padding: $input.css('padding'),
                        lineHeight: $input.css('line-height'),
                        font: $input.css('font'),
                        fontSize: $input.css('font-size'),
                        position: 'absolute',
                        wordWrap: 'break-word',
                        top: -100000
                    });
                $input.bind('keyup blur', function(e) {
                    $input._autoResize.html($input.val().split('\n').join('<br/> '));
                    $input.css({
                        height: $input._autoResize.height() + 10
                    });
                });
            }
        });
    };
})(jQuery);

// Дропдауны
(function($) {
    var CLASS_ACTIVE = 'active';
    var CLASS_MENU = 'ui-dropdown-menu';
    var CLASS_MENU_ITEM = 'ui-dropdown-menu-item';
    var CLASS_MENU_ITEM_ACTIVE = 'active';
    var CLASS_MENU_ICON = 'icon';
    var CLASS_MENU_ICON_LEFT = 'icon-left';
    var CLASS_MENU_ICON_RIGHT = 'icon-right';
    var CLASS_MENU_ITEM_WITH_ICON_LEFT = 'icon-left';
    var CLASS_MENU_ITEM_WITH_ICON_RIGHT = 'icon-right';

    var methods = {
        init: function(parameters) {
            return this.each(function() {
                var defaults = {
                    el: $(this),
                    type: 'normal',
                    width: '',
                    addClass: '',
                    position: 'left',
                    iconPosition: 'left',
                    openEvent: 'mousedown',
                    closeEvent: 'click', // Not use
                    data: [{}],
                    onchange: function() {},
                    oncreate: function() {},
                    onopen: function() {},
                    onclose: function() {}
                };
                var t = this;
                var p = t.p = $.extend(defaults, parameters);
                var $el = p.el;

                if ($el.data('menu') || !p.data) return false;

                $el.data('menu', $('<div></div>').attr({class: CLASS_MENU + ' ' + p.addClass}).appendTo('body'));
                $(p.data).each(function(i, item) {
                    var $item = $('<div>' + item.title + '</div>').attr({class: CLASS_MENU_ITEM});
                    if (item.icon) {
                        var $icon = $('<div><img src="' + item.icon + '" /></div>');
                        $item.append($icon);
                        if (p.iconPosition == 'left') {
                            $icon.attr({class: CLASS_MENU_ICON + ' ' + CLASS_MENU_ICON_LEFT});
                            $item.addClass(CLASS_MENU_ITEM_WITH_ICON_LEFT);
                        } else {
                            $icon.attr({class: CLASS_MENU_ICON + ' ' + CLASS_MENU_ICON_RIGHT});
                            $item.addClass(CLASS_MENU_ITEM_WITH_ICON_RIGHT);
                        }
                    }
                    if (item.isActive) {
                        $item.addClass(CLASS_MENU_ITEM_ACTIVE);
                    }
                    $item.mouseup(function() {
                        if (p.type == 'checkbox') {
                            $el.data('menu').find('.' + CLASS_MENU_ITEM).removeClass(CLASS_MENU_ITEM_ACTIVE);
                            $item.addClass(CLASS_MENU_ITEM_ACTIVE);
                        }
                        close();
                        run(p.onchange, $el, item);
                    });
                    $el.data('menu').append($item);
                });

                $el.bind(p.openEvent, function(e) {
                    e.stopPropagation();
                    if (!$el.data('menu').is(':visible')) {
                        $('html, body').trigger(p.openEvent);
                        open();
                    } else {
                        close();
                    }
                });

                $el.data('menu').bind(p.openEvent, function(e) {
                    e.stopPropagation();
                });

                $('html, body').bind(p.openEvent, function(e) {
                    close();
                    run(p.onclose, $el, $el.data('menu'));
                });

                function open() {
                    $el.addClass(CLASS_ACTIVE);
                    $el.data('menu').css({
                        width: p.width || $el.width()
                    });

                    var $menu = $el.data('menu');
                    var isFixed = !!($menu.css('position') == 'fixed');
                    var offset = $el.offset();
                    var offsetTop = offset.top;
                    var offsetLeft = offset.left
                        + parseFloat($menu.css('margin-left'))
                        - parseFloat($menu.css('margin-right'));
                    if (p.position == 'right') {
                        offsetLeft += ($el.width() - $menu.width())
                    }
                    if (isFixed) {
                        offsetTop -= $(document).scrollTop();
                        offsetLeft -= $(document).scrollLeft();
                    }

                    $menu.css({
                        top: offsetTop + $el.outerHeight(),
                        left: offsetLeft
                    }).show();
                    run(p.onopen, $el, $el.data('menu'));
                }

                function close() {
                    $el.removeClass(CLASS_ACTIVE);
                    $el.data('menu').hide();
                    run(p.onclose, $el, $el.data('menu'));
                }

                run(p.oncreate, $el);
            });

            function run(f, context, argument) {
                if ($.isFunction(f)) {
                    f.call(context, argument);
                }
            }
        },

        menu: function() {
            return this.each(function() {
                return 123;
//                return $(this).data('menu');
            });
        }
    };

    $.fn.dropdown = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.dropdown');
        }
    };
})(jQuery);

$.extend(Events, Eventlist);
delete(Eventlist);

var Elements = {
    initImages: function(selector){
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

        //логика картинок топа
        $("div.post-image-top img").bind("load", function () {
            var src = $(this).attr('src');
            var img = new Image();
            var link = $(this).closest(".post").find('.ajax-loader-ext');

            img.onload = function() {
                if (this.width < 250 && this.height < 250) {
                    //small
                    Elements.initLinkLoader(link, true);
                } else {
                    //big
                    Elements.initLinkLoader(link, false);
                }
            };

            img.src = src;
        });

        $(".left-panel .timestamp").easydate({
            date_parse: function(date) {
                if (!date) return;
                var d = date.split('.');
                var i = d[1];
                d[1] = d[0];
                d[0] = i;
                var date = d.join('.');
                return Date.parse(date);
            },
            uneasy_format: function(date) {
                return date.toLocaleDateString();
            }
        });
        $('.left-panel .images-ready').imageComposition();
        $('.right-panel .images').imageComposition('right');
    },
    addEvents: function(){
        (function(){
            $(".slot .post .content").addClass("dragged");
            var target = false;
            var dragdrop = function(post, slot, queueId, callback, failback){
                Events.fire('post_moved', [post, slot, queueId, function(state, newId){
                    if (state) {
                        callback(newId);
                    } else {
                        failback();
                    }
                }]);
            };

            var draggableParams = {
                revert: 'invalid',
                appendTo: 'body',
                cursor: 'move',
                cursorAt: {left: 100, top: 20},
                helper: function() {
                    return $('<div/>').html('Укажите, куда поместить пост...').addClass('moving dragged');
                },
                start: function() {
                    var self = $(this),
                        $post = self.closest('.post');
                    $post.addClass('moving');
                },
                stop: function() {
                    var self = $(this),
                        $post = self.closest('.post');
                    $post.removeClass('moving');
                }
            };

            $(".post > .content").draggable(draggableParams);

            $('.items .slot').droppable({
                activeClass: "ui-state-active",
                hoverClass: "ui-state-hover",

                drop: function(e, ui) {
                    var target = $(this),
                        post = $(ui.draggable).closest('.post'),
                        slot = post.closest('.slot'),
                        helper = $(ui.helper);

                    if (target.hasClass('empty')) {
                        dragdrop(post.data("id"), target.data("id"), post.data("queue-id"), function(newId){
                            if (post.hasClass('movable')) {
                                target.html(post);
                            } else {
                                var copy = post.clone();
                                copy.addClass("dragged");
                                target.html(copy);
                                copy.draggable(draggableParams);
                            }
                            slot.addClass('empty');
                            target.removeClass('empty');

                            target.find('.post').data("id", newId).data("queue-id", newId);
                        },function(){

                        });
                    }
                }
            });
        })();
    },
    leftdd: function(){
        return $("select").multiselect("getChecked").map(function(){
            return this.value;
        }).get();
    },
    rightdd:function(value){
        if (typeof value == 'undefined') {
            return $("#right-drop-down").data("selected");
        } else {
            console.log(value);
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
    initLinkLoader: function(obj, full){
        var container   = obj.parents('div.link-info-content');
        var link        = obj.attr('rel');
        $.ajax({
            url: controlsRoot + 'parse-url/',
            type: 'GET',
            dataType : "json",
            data: {
                url: link
            },
            success: function (data) {
                if (full) {
                    container.html(linkTplFull);
                } else {
                    container.html(linkTplShort);
                }

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

                var matches = link.match(pattern);

                shortLink = link;
                if (matches[2]) {
                    shortLink = matches[2];
                }
                container.find('div.link-status-content span a').text(shortLink);
            }
        });
    },
    initLinks: function(){
        $('img.ajax-loader').each(function(){
            Elements.initLinkLoader($(this), true);
        });
    }
};

$.fn.dd_sel = function(id){
    var elem = $(this);
    if(!elem.hasClass("drop-down")) return;
    if(id) {
        elem = elem.find("li[data-id=" + id + "]");
    } else {
        elem = elem.find("li:first");
    }

    $(this).find('li.active').removeClass('active');
    elem.addClass('active');

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