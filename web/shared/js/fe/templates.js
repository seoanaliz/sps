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
