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

 var BOX_AUTHOR =
'<div class="photo" style="float: left; margin-right: 10px; height: 100px;">' +
    '<a href="http://vk.com/id<?=user.id?>" target="_blank">' +
        '<img src="<?=user.photo?>" alt="" />' +
    '</a>' +
'</div>' +
'<div class="info">' +
    '<?=text?>' +
'</div>';

 var BOX_ADD_AUTHOR =
'Вы действительно хотите назначить ' +
'<a href="http://vk.com/id<?=user.id?>" target="_blank">' +
    '<?=user.name?>' +
'</a>' +
' автором?';

 var BOX_DELETE_AUTHOR =
'Вы действительно хотите удалить ' +
'<a href="http://vk.com/id<?=user.id?>" target="_blank">' +
    '<?=user.name?>' +
'</a>' +
' из списка авторов?';

var QUEUE_SLOT_ADD =
'<div class="new slot empty">' +
    '<div class="slot-header">' +
        '<span class="time">__:__</span>' +
        '<span class="datepicker"></span>' +
    '</div>' +
'</div>';

var ATTACHMENT_PREVIEW_REPOST =
'<div class="attachment post">' +
    '<div class="l d-hide">' +
        '<div class="userpic">' +
            '<img src="<?=owner.photo?>" />' +
        '</div>' +
    '</div>' +
    '<div class="name">' +
        '<a href="http://vk.com/<?=owner.screen_name?>" target="_blank">' +
            '<?=owner.name?>' +
        '</a>' +
    '</div>' +
    '<div class="content">' +
        '<?=text?>' +
    '</div>' +
    '<a class="delete-attachment">удалить</a>' +
'</div>';

var ATTACHMENT_PREVIEW_LINK =
'<div class="attachment link-info">' +
    '<div class="link-description">' +
        '<? if (isset("image")) { ?>' +
            '<div class="post_describe_image" title="Редактировать картинку" style="background: no-repeat center url(<?=image?>);"></div>' +
        '<? } ?>' +
        '<div class="post_describe_layout">' +
            '<? if (isset("title")) { ?>' +
                '<div class="post_describe_header">' +
                    '<a href="<?=link?>" target="_blank" title="Редактировать заголовок">' +
                        '<span><?=title?></span>' +
                    '</a>' +
                    '<input type="text" id="post_header">' +
                '</div>' +
            '<? } ?>' +
            '<? if (isset("description")) { ?>' +
                '<p title="Редактировать описание">' +
                    '<span><?=description?></span>' +
                    '<textarea id="post_description"></textarea>' +
                '</p>' +
            '<? } ?>' +
        '</div>' +
    '</div>' +
    '<div class="link-status">' +
        '<span>' +
            'Ссылка: <a href="<?=link?>" target="_blank"><?=text?></a>' +
            '<a class="delete-attachment">удалить</a>' +
        '</span>' +
    '</div>' +
'</div>';