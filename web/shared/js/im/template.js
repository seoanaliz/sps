var MAIN =
'<div id="left-column"></div>' +
'<div id="right-column"></div>';

var TABS =
'<div class="tab-bar messenger">' +
    '<? if (isset("listTab")) { ?>' +
        '<? each(TABS_ITEM_LIST, [listTab]); ?>' +
    '<? } ?>' +
    '<? if (isset("dialogTab")) { ?>' +
        '<? each(TABS_ITEM_DIALOG, [dialogTab]); ?>' +
    '<? } ?>' +
    '<div class="filter"><a data-filtered="Показать все диалоги" data-not-filtered="Показать только непрочитанные">Показать только непрочитанные</a></div>' +
'</div>';

var TABS_ITEM_LIST =
'<div class="tab list<?=(isset("isSelected") && isSelected) ? " selected" : ""?>" data-id="<?=id?>">' +
    '<?=label?>' +
'</div>';

var TABS_ITEM_DIALOG =
'<div class="tab dialog<?=(isset("isSelected") && isSelected) ? " selected" : ""?>" data-id="<?=id?>">' +
    '<?=label?>' +
    '<? if (isset("isOnline") && isOnline) { ?>' +
        '<div class="icon online"></div>' +
    '<? } else { ?>' +
        '<div class="icon offline"></div>' +
    '<? } ?>' +
    '<? if (isset("isOnList") && isOnList) { ?>' +
        '<div class="icon select"></div>' +
    '<? } else { ?>' +
        '<div class="icon plus"></div>' +
    '<? } ?>' +
'</div>';

var LEFT_COLUMN =
'<div class="header fixed"></div>' +
'<div class="list">' +
    '<div id="list-messages"></div>' +
    '<div id="list-dialogs"></div>' +
'</div>';

var DIALOGS =
'<div class="dialogs" data-id="<?=id?>">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<? each(DIALOGS_ITEM, list); ?>' +
    '<? } else { ?>' +
        '<div class="empty">Список диалогов пуст</div>' +
    '<? } ?>' +
'</div>';

var DIALOGS_LOADING =
'<div class="dialogs">' +
    '<div class="load"></div>' +
'</div>';

var DIALOGS_ITEM =
'<? var isNew = isset("isNew") && isNew; ?>' +
'<div class="dialog clear-fix<?=(isNew && !isViewer) ? " new" : ""?>" data-id="<?=id?>" data-title="<?=user.name?>" data-user-id="<?=user.id?>" data-message-id="<?=messageId?>">' +
    '<div class="user">' +
        '<div class="photo">' +
            '<a href="http://vk.com/id<?=user.id?>" target="_blank"><img src="<?=user.photo?>" alt="" /></a>' +
        '</div>' +
        '<div class="info clear-fix">' +
            '<div class="name">' +
                '<a href="http://vk.com/id<?=user.id?>" target="_blank"><?=user.name?></a>' +
            '</div>' +
            '<? if (user.isOnline) { ?>' +
                '<div class="status">Online</div>' +
            '<? } ?>' +
            '<div class="date">' +
                '<?=timestamp?>' +
            '</div>' +
        '</div>' +
    '</div>' +
    '<div class="history">' +
        '<? if (isset("text")) { ?>' +
            '<? if (isViewer) { ?>' +
                '<div class="from-me clear-fix<?=isNew ? " new" : ""?>">' +
                    '<div class="photo">' +
                        '<img src="<?=viewer.photo?>" alt="" />' +
                    '</div> ' +
                    '<div class="body">' +
                        '<?=text?>' +
                    '</div> ' +
                '</div>' +
            '<? } else { ?>' +
                '<?=text?>' +
            '<? } ?>' +
        '<? } else { ?>' +
            '...' +
        '<? } ?>' +
    '</div>' +
    '<div class="actions">' +
        '<? if (lists.length) { ?>' +
            '<div class="action icon select"></div>' +
        '<? } else { ?>' +
            '<div class="action icon plus"></div>' +
        '<? } ?>' +
    '</div>' +
'</div>';

var MESSAGES =
'<div class="messages" data-id="<?=id?>">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<? if (isset("isLoad") && isLoad) { ?>' +
            '<div class="mini-load"></div>' +
        '<? } ?>' +
        '<? each(MESSAGES_ITEM, list); ?>' +
    '<? } else { ?>' +
        '<div class="empty">История сообщений пуста</div>' +
    '<? } ?>' +
'</div>' +
'<div class="post-message clear-fix fixed">' +
    '<div class="left-column">' +
        '<div class="photo">' +
            '<a target="_blank" href="http://vk.com/id<?=viewer.id?>" title="Это Вы">' +
                '<img src="<?=viewer.photo?>" alt="" />' +
            '</a>' +
        '</div>' +
    '</div>' +
    '<div class="center-column">' +
        '<div class="content">' +
            '<div class="textarea-wrap">' +
                '<textarea rows="" cols="" placeholder="Введите ваше сообщение..."></textarea>' +
            '</div>' +
            '<div class="actions">' +
                '<button class="button send">Отправить</button>' +
                '<a class="link save-template">Создать шаблон</button>' +
            '</div>' +
        '</div>' +
    '</div>' +
    '<div class="right-column">' +
        '<div class="photo">' +
            '<a target="_blank" href="http://vk.com/id<?=user.id?>" title="<?=user.name?>">' +
                '<img src="<?=user.photo?>" alt="" />' +
            '</a>' +
        '</div>' +
    '</div>' +
'</div>';

var MESSAGES_LOADING =
'<div class="messages">' +
    '<? if (isset("preloadList") && preloadList.length) { ?>' +
        '<div class="mini-load"></div>' +
        '<? each(MESSAGES_ITEM, preloadList); ?>' +
    '<? } else { ?>' +
        '<div class="load"></div>' +
    '<? } ?>' +
'</div>' +
'<div class="post-message clear-fix fixed">' +
    '<div class="left-column">' +
        '<div class="photo">' +
            '<a target="_blank" href="http://vk.com/id<?=viewer.id?>" title="Это Вы">' +
                '<img src="<?=viewer.photo?>" alt="" />' +
            '</a>' +
        '</div>' +
    '</div>' +
    '<div class="center-column">' +
        '<div class="content">' +
            '<div class="textarea-wrap">' +
                '<textarea rows="" cols="" placeholder="Введите ваше сообщение..."></textarea>' +
            '</div>' +
            '<div class="actions">' +
                '<button class="button send">Отправить</button>' +
                '<a class="link save-template">Создать шаблон</button>' +
            '</div>' +
        '</div>' +
    '</div>' +
    '<div class="right-column">' +
        '<div class="photo">' +
            '<a target="_blank" href="http://vk.com/id<?=user.id?>" title="<?=user.name?>">' +
                '<img src="<?=user.photo?>" alt="" />' +
            '</a>' +
        '</div>' +
    '</div>' +
'</div>';

var MESSAGES_ITEM =
'<? var isNew = isset("isNew") && isNew; ?>' +
'<? var isViewer = isset("isViewer") && isViewer; ?>' +
'<? var user = isViewer ? viewer : user; ?>' +
'<div class="message clear-fix<?=isNew ? " new" : ""?><?=isViewer ? " viewer" : " user"?>" data-id="<?=id?>">' +
    '<div class="left-column">' +
        '<div class="photo">' +
            '<a target="_blank" href="http://vk.com/id<?=user.id?>">' +
                '<img src="<?=user.photo?>" alt="">' +
            '</a>' +
        '</div>' +
    '</div>' +
    '<div class="center-column">' +
        '<div class="content">' +
            '<div class="title">' +
                '<a target="_blank" href="http://vk.com/id<?=user.id?>"><?=user.name?></a>' +
            '</div>' +
            '<div class="text"><?=text?></div>' +
            '<? if (isset("attachments")) { ?>' +
                '<div class="attachments clear-fix">' +
                    '<? each(MESSAGE_ATTACHMENT, attachments); ?>' +
                '</div>' +
            '<? } ?>' +
        '</div>' +
    '</div>' +
    '<div class="right-column">' +
        '<div class="date"><?=timestamp?></div>' +
    '</div>' +
'</div>';

var MESSAGE_ATTACHMENT_PHOTO =
'<a target="_blank" href="<?=isset("src_xxxbig") ? src_xxxbig : src_big?>">' +
    '<img src="<?=src_big?>" alt="" />' +
'</a>';

var MESSAGE_ATTACHMENT_AUDIO =
'<div>' +
    '<a target="_blank" href="http://vk.com/audio?id=<?=owner_id?>&audio_id=<?=aid?>">' +
        '♫ <?=performer?> - <?=title?>' +
    '</a>' +
'</div>';

var MESSAGE_ATTACHMENT_VIDEO =
'<a target="_blank" href="http://vk.com/video<?=owner_id?>_<?=vid?>" style="height: 240px">' +
    '<img src="<?=image_big?>" alt="" />' +
'</a>';

var MESSAGE_ATTACHMENT_DOC =
'<? if (isset("thumb")) { ?>' +
    '<a target="_blank" href="<?=url?>">' +
        '<img src="<?=thumb?>" alt="" />' +
    '</a>' +
'<? } else { ?>' +
    '<div>' +
        'Документ: <a target="_blank" href="<?=url?>"><?=title?></a>' +
    '</div>' +
'<? } ?>';

var MESSAGE_ATTACHMENT =
'<? if (type == "photo") { ?>' +
    '<div class="photos">' +
        '<? each(MESSAGE_ATTACHMENT_PHOTO, list); ?>' +
    '</div>' +
'<? } else if (type == "audio") { ?>' +
    '<div class="audios">' +
        '<? each(MESSAGE_ATTACHMENT_AUDIO, list); ?>' +
    '</div>' +
'<? } else if (type == "doc") { ?>' +
    '<div class="documents">' +
        '<? each(MESSAGE_ATTACHMENT_DOC, list); ?>' +
    '</div>' +
'<? } else if (type == "video") { ?>' +
    '<div class="videos">' +
        '<? each(MESSAGE_ATTACHMENT_VIDEO, list); ?>' +
    '</div>' +
'<? } else { ?>' +
    '<div>' +
        '[attach: <?=type?>]' +
    '</div>' +
'<? } ?>';


var RIGHT_COLUMN =
'<div class="header">' +
    '<div class="tab-bar">' +
        '<div class="tab selected">Контакты</div>' +
    '</div>' +
'</div>' +
'<div class="list scroll-like-mac">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<? each(LIST_ITEM, list); ?>' +
    '<? } else { ?>' +
        '<div class="empty">Список пуст</div>' +
    '<? } ?>' +
'</div>';

var LIST_ITEM =
'<div class="<?=isset("isDraggable") && isDraggable ? "drag-wrap" : ""?>">' +
    '<? if (isset("title")) { ?>' +
        '<div class="item" data-id="<?=id?>" data-title="<?=title?>">' +
            '<div class="title<?=isset("isRead") && isRead ? "" : " new"?><?=isset("isSelected") && isSelected ? " active" : ""?>">' +
                '<span class="text"><?=title?></span>' +
                '<? if (isset("isEditable") && isEditable) { ?>' +
                    '<span class="icon edit"></span>' +
                    '<span class="icon delete"></span>' +
                '<? } ?>' +
                '<span class="counter"><?=isset("counter") && counter ? "+" + counter : ""?></span>' +
            '</div>' +
        '</div>' +
    '<? } ?>' +
'</div>';

var SAVE_TEMPLATE_BOX =
'<div class="box-templates">' +
    '<div class="add-template-closed">' +
        '<div class="input-wrap">' +
            '<input type="text" placeholder="Написать новый ответ..." />' +
        '</div>' +
    '</div>' +
    '<div class="add-template-opened">' +
        '<div class="title">' +
            'Выберите списки' +
        '</div>' +
        '<div class="input-wrap">' +
            '<input class="lists" type="text"/>' +
        '</div>' +
        '<div class="title">' +
            'Введите текст шаблона' +
        '</div>' +
        '<div class="input-wrap">' +
            '<textarea class="template-text"><?=text?></textarea>' +
        '</div>' +
        '<div class="actions">' +
            '<button class="button save-template">Сохранить</button>' +
            '<button class="button cancel">Отменить</button>' +
        '</div>' +
    '</div>' +
    '<div class="template-list loading"></div>' +
'</div>';

var TEMPLATE_LIST =
'<? each(TEMPLATE_LIST_ITEM, list); ?>';

var TEMPLATE_LIST_ITEM =
'<div class="message clear-fix" data-id="<?=id?>">' +
    '<div class="left-column">' +
        '<div class="photo">' +
            '<a target="_blank" href="http://vk.com/id<?=user.id?>">' +
                '<img src="<?=user.photo?>" alt="">' +
            '</a>' +
        '</div>' +
    '</div>' +
    '<div class="center-column">' +
        '<div class="content">' +
            '<div class="title">' +
                '<a target="_blank" href="http://vk.com/id<?=user.id?>"><?=user.name?></a>' +
                '<? each(TEMPLATE_LIST_ITEM_LISTS, lists); ?>' +
            '</div>' +
            '<div class="text"><?=text?></div>' +
        '</div>' +
    '</div>' +
    '<div class="right-column">' +
        '<div class="actions">' +
            //'<div class="icon edit"></div>' +
            '<div class="icon delete"></div>' +
        '</div>' +
    '</div>' +
'</div>';

var TEMPLATE_LIST_ITEM_LISTS =
'<span class="tag"><?=title?></span>';