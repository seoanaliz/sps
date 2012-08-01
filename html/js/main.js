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
                '<div class="save button l">Отправить</div>' +
                '<a href="#" class="cancel l">Отменить</a>' +
                '<a href="#" class="qq-upload-button">Прикрепить</a>' +
                '</div>',
            onComplete: function(id, fileName, responseJSON) {
                var $deleteAttachLink = $('<a />', { 'href': 'javascript:;', 'text': 'удалить', 'class': 'delete-attach' });
                $deleteAttachLink.click(function(e) {
                    e.preventDefault();
                    $(this).closest('li').remove();
                });
                $('.qq-upload-list li:last-child')
                    .prepend($('<img />', { src: responseJSON.image }))
                    .prepend($deleteAttachLink);
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
        };
        var click_li = function(e){
            e.stopPropagation();
            elem.dd_sel($(this).data("id"));
            hidethis();
        };
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
//        .delegate('a.edit', 'click', function() {
//            var elem = $(this).closest(".post"),
//                pid = elem.data("id"),
//                content = elem.find('.content'),
//                showCut = content.find('.show-cut'),
//                shortСut = content.find('.shortcut'),
//                editPanel = elem.find('.bottom.edit'),
//                bottomPanel = elem.find('.bottom.d-hide');
//
//            Events.fire('leftcolumn_editpost', [pid, function(state) {
//                if (showCut.get(0)) showCut.click();
//                var text = shortСut.html();
//
//                function save(e) {
//                    editPanel.hide();
//                    bottomPanel.show();
//                    shortСut.attr('contenteditable', false)
//                        .unbind('blur')
//                        .blur();
//                }
//                function cancel(e) {
//                    editPanel.hide();
//                    bottomPanel.show();
//                    shortСut.attr('contenteditable', false)
//                        .unbind('blur')
//                        .html(text)
//                        .blur();
//                }
//
//                shortСut.attr('contenteditable', true).focus();
//                bottomPanel.hide();
//                window.getSelection().collapse(shortСut.get(0), shortСut.text().length);
//                if (!editPanel.get(0)) {
//                    editPanel = $('<div class="bottom edit"/>')
//                        .appendTo(elem)
//                        .append($('<a href="javascript:;">Сохранить</a>').click(save))
//                        .append($('<a href="javascript:;">Отменить</a>').click(cancel))
//                } else {
//                    editPanel.show();
//                }
//            }]);
//        });

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
        };
        var getDD = function(elem){
            return $(elem).closest(".header").find(".drop-down");
        };
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
                                    $linkDescription.empty();
                                    $linkStatus.empty();

                                    // отрисовываем ссылку
                                    if (result.img) {
										var $imgBlock = $('<div></div>',{'class':'post_describe_image','title':'Редактировать картинку'}).css(
											{
												'background-image' : 'url('+result.img+')'
											}
										),
										$descriptionLayout = $('<div></div>',{'class':'post_describe_layout'});
                                        $linkDescription.append($imgBlock);
										$linkDescription.append($descriptionLayout);
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
									editPostDescribeLink.load($h,$p,$imgBlock,result.img);

                                    var $span = $('<span />', { text: 'Ссылка: ' });
                                    $span.append($('<a />', { href: 'http://' + foundLink, target: '_blank', text: foundDomain }));

                                    var $deleteLink = $('<a />', { href: 'javascript:;', 'class': 'delete-link', text: 'удалить' }).click(function() {
                                        // убираем аттач ссылки
                                        $linkDescription.empty();
                                        $linkStatus.empty();
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
				this.image.click(function() {
					t.editImage(t.description);
					return false;
				});
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
					$(this).Jcrop({
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
						action: 'upload.php',
						template: ' <div class="qq-uploader">' +
									'<ul class="qq-upload-list"></ul>' +
									'<a href="#" class="button spr qq-upload-button">Загрузить картинку</a>' +
									'</div>',
						onComplete: function(id, fileName, responseJSON) {
							t.originalImage.attr({src:responseJSON.image});
							t.previewImage.attr({src:responseJSON.image});
							t.crop();
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
						coords: t.coords
					};
				console.log(data);
			}
		};
        var stop = function(){
            $(window).unbind("click", stop);
            if(!input.text().length) form.addClass("collapsed");
        };
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

//    (function(){
//        var form = $(".newpost"),
//            input = $(".input", form);
//        input.focus(function(){
//            form.removeClass("collapsed");
//        });
//        input.blur(function(e) {
//            if(!input.val().length) form.addClass("collapsed");
//        });
//        input.bind('keydown keyup focus', function(e) {
//            if (!input.autoResize) {
//                input.autoResize = $('<div/>')
//                    .appendTo('body')
//                    .css({
//                        width: input.width(),
//                        minHeight: input.height(),
//                        padding: input.css('padding'),
//                        lineHeight: input.css('line-height'),
//                        font: input.css('font'),
//                        fontSize: input.css('font-size'),
//                        position: 'absolute',
//                        top: -10000
//                    });
//            }
//            input.autoResize.html(input.val().split('\n').join('<br/>$nbsp;'));
//            input.css({
//                height: input.autoResize.height() + 20
//            });
//        });
//        form.find(".save").click(function(){
//            form.addClass("spinner");
//            Events.fire("post", [
//                input.val(),
//                input.data("id"),
//                function(state){
//                    if(state) {
//                        input.data("id", 0);
//                        input.val('').blur();
//                    }
//                    form.removeClass("spinner");
//                }
//            ])
//        });
//        form.find('.cancel').click(function(e) {
//            input.val('').blur();
//            form.addClass('collapsed');
//            e.preventDefault();
//        });
//        form.find(".attach").click(
//            /*TODO: attach*/
//        );
//
//        $(".left-panel").delegate(".post .edit", "click" ,function(){
//            /*TODO: edit*/
//            input.data("id", $(this).closest("post").data("id"));
//        });
//    })();

    $('.left-panel .show-cut').click(function(e) {
        var $content = $(this).closest('.content'),
            $shortcut = $content.find('.shortcut'),
            shortcut = $shortcut.html(),
            cut      = $content.find('.cut').html();

        $shortcut.html(shortcut + ' ' + cut);
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

    Elements.addEvents();
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
};
$.extend(Events, Eventlist);
delete(Eventlist);

var Elements = {
    addEvents: function(){
        (function(){
            $(".slot .post .content").addClass("dragged");
            var target = false;
            var dragdrop = function(post, slot, callback, failback){
                Events.fire('post_moved', [post, slot, function(state){
                    (state ? callback : failback)();
                }]);
            };

            var draggableParams = {
                revert: 'invalid',
                appendTo: 'body',
                cursor: 'move',
                cursorAt: {left: 5, top: 0},
                helper: function() {
                    return $('<div/>').html('Укажите, куда поместить пост...').addClass('moving dragged');
                },
                start: function() {
                    var self = $(this),
                        post = self.closest('.post');
                    console.log(self);
                },
                stop: function() {
                    var self = $(this),
                        post = self.closest('.post');
                    console.log(self);
                }
            };

            $(".post").draggable(draggableParams);

            $('.items .slot').droppable({
                activeClass: "ui-state-active",
                hoverClass: "ui-state-hover",

                drop: function(e, ui) {
                    var target = $(this),
                        post = $(ui.draggable),
                        slot = post.closest('.slot'),
                        helper = $(ui.helper);

                    if (target.hasClass('empty')) {
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
                    }
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
};

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