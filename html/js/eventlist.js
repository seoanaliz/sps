var Eventlist = {
    leftcolumn_deletepost: function(post_id, callback){callback(1)},
    leftcolumn_recoverpost: function(post_id, callback){callback(1)},
    leftcolumn_editpost: function(post_id, callback){callback(1)},

    rightcolumn_deletepost: function(post_id, callback){callback(1)},

    leftcolumn_dropdown_change: function(){},
    rightcolumn_dropdown_change: function(){},
    calendar_change: function(){},

    wall_load_more: function(callback){callback(false/*false - nothing more to load*/);},
    post_moved: function(post_id, slot_id, callback){
        window.setTimeout(function(){callback(1)},1000);
    },

    /* после выполнения запроса к сервису. Вызвать callback(state) state = {}|false */
    leftcolumn_source_edited: function(val,id, callback){callback({value: val});},
    leftcolumn_source_deleted: function(id, callback){callback(true)},
    leftcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    rightcolumn_source_edited: function(val,id, callback){callback({value: val});},
    rightcolumn_source_deleted: function(id, callback){callback(true)},
    rightcolumn_source_added: function(val, callback){callback({value: val, id: parseInt(Math.random()*100)})},

    post_describe_link: function(link, callback) {
        if (link == 'link.ru') {
            callback({
                title: 'Секреты личной жизни Влада Цыплухин - ЭКСКЛЮЗИВ',
                description: 'Эксклюзивный материал для программы Дарьи Герман и Николая Воробьёва Школа Соблазна'
            });
        } else if (link == 'link.img.ru') {
            callback({
                img: 'img/ae.png',
                title: 'Секреты личной жизни Влада Цыплухин - ЭКСКЛЮЗИВ',
                description: 'Эксклюзивный материал для программы Дарьи Герман и Николая Воробьёва Школа Соблазна'
            });
        } else {
            callback({
                description: 'Эксклюзивный материал для программы Дарьи Герман и Николая Воробьёва Школа Соблазна'
            });
        }

    },

    post: function(html, id, callback) {
        // id = 0 - new post, else - edit old
        callback(false);
    },

    eof: null
}

/*
Elements.calendar()
Elements.calendar(value)

Elements.leftdd()
Elements.leftdd(value)

Elements.rightdd()
Elements.rightdd(value)

Elements.addEvents()
*/