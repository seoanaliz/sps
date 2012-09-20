var MAIN =
'<div class="left-column"></div>' +
'<div class="right-column"></div>';

var TABS =
'<div class="tab-bar">' +
    '<? each(TABS_ITEM, tabs); ?>' +
'</div>';

var TABS_ITEM =
'<div data-id="<?=id?>" class="tab<?=(isset("isSelected") && isSelected) ? " selected" : ""?>"><?=title?></div>';

var LEFT_COLUMN =
'<div class="header fixed"></div>' +
'<div class="list"></div>';

var RIGHT_COLUMN =
'<div class="header">' +
    '<div class="tab-bar">' +
        '<div class="tab selected">Контакты</div>' +
    '</div>' +
'</div>' +
'<div class="list scroll-like-mac"></div>';

var DIALOGS =
'<div class="dialogs" data-id="<?=id?>">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<?=tmpl(DIALOGS_BLOCK, {id: 0, list: list})?>' +
    '<? } else if (isset("isLoad") && isLoad) { ?>' +
        '<div class="load"></div>' +
    '<? } else { ?>' +
        '<div class="empty">Список диалогов пуст</div>' +
    '<? } ?>' +
'</div>';

var DIALOGS_BLOCK =
'<div class="dialogs-block<?=id?>">' +
    '<? each(DIALOGS_ITEM, list); ?>' +
'</div>';

var DIALOGS_ITEM =
'<? var isNew = isset("isNew") && isNew; ?>' +
'<div class="dialog clear-fix<?=(isNew && !isViewer) ? " new" : ""?>" data-id="<?=id?>" data-title="<?=user.name?>" data-user-id="<?=user.id?>">' +
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
        '<?=tmpl(MESSAGES_BLOCK, {id: 0, list: list})?>' +
    '<? } else if (isset("isLoad") && isLoad) { ?>' +
        '<div class="load"></div>' +
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
            '<textarea rows="" cols="" placeholder="Введите ваше сообщение..."></textarea>' +
            '<div class="actions">' +
                '<button class="button send">Отправить</button>' +
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

var MESSAGES_BLOCK =
'<div class="messages-block<?=id?>">' +
    '<? each(MESSAGES_ITEM, list); ?>' +
'</div>';

var MESSAGES_ITEM =
'<? var isNew = isset("isNew") && isNew; ?>' +
'<? var isViewer = isset("isViewer") && isViewer; ?>' +
'<div class="message clear-fix<?=isNew ? " new" : ""?><?=isViewer ? " viewer" : ""?>" data-id="<?=id?>">' +
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
'<a target="_blank" href="<?=isset("src_xxxbig") ? src_xxxbig : src_big?>" style="height: <?=height?>px">' +
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

var LIST =
'<? if (isset("list") && list.length) { ?>' +
    '<div class="item" data-id="999999" data-title="Не в списке">' +
        '<div class="title active">' +
            'Не в списке' +
            '<span class="counter"><?=count ? "+" + count : ""?></span>' +
        '</div>' +
    '</div>' +
    '<? each(LIST_ITEM, list); ?>' +
'<? } else { ?>' +
    '<div class="empty">Список пуст</div>' +
'<? } ?>';

var LIST_ITEM =
'<div class="item" data-id="<?=id?>" data-title="<?=title?>">' +
    '<div class="title">' +
        '<?=title?>' +
        '<span class="counter"><?=count ? "+" + count : ""?></span>' +
    '</div>' +
    '<? if (isset("dialogs") && dialogs.length) { ?>' +
        '<div class="list">' +
            '<? each(LIST_ITEM_DIALOG, dialogs); ?>' +
        '</div>' +
    '<? } ?>' +
'</div>';

var LIST_ITEM_DIALOG =
'<div class="dialog" data-id="<?=id?>" data-title="<?=user.name?>">' +
    '<div class="icon"><img src="<?=user.photo?>" alt="" /></div>' +
    '<div class="title"><?=user.name?><span class="counter"></span></div>' +
'</div>';
