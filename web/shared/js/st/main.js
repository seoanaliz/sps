/**
 * Events
 */
var Events = {
    eventList: {},
    fire: function(name, args){
        if (typeof args != "undefined") {
            if(!$.isArray(args)) args = [args];
        } else {
            args = [];
        }
        if ($.isFunction(this.eventList[name])) {
            try {
                this.eventList[name].apply(window, args);
            } catch(e) {
                if(console && $.isFunction(console.log)) {
                    console.log(e);
                }
            }
        }
    }
};
var Eventlist = {
    load_stat: function(offset, limit, callback) {
        callback(false);
    }
};
$.extend(Events.eventList, Eventlist);

/**
 * Test data
 */
var Data = {
    rows: [
        {
            publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
            publicName: 'Путешествия',
            publicFollowers: '271 375',
            publicGrowthNum: '1231',
            publicGrowthPer: '0.53%',
            user: {
                userId: 1,
                userName: 'Саша Радославов',
                userPhoto: 'http://app.uxpin.com/u/a/0/9/a09e2c748e10258cd892f11307202c77/e_d7f264dc.jpg',
                userDescription: 'Сотрудничество'
            },

            etc: false
        },
        {
            publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
            publicName: 'Креативные идеи',
            publicFollowers: '271 375',
            publicGrowthNum: '231',
            publicGrowthPer: '0.53%',
            user: {
                userId: 1,
                userName: 'Саша Радославов',
                userPhoto: 'http://app.uxpin.com/u/a/0/9/a09e2c748e10258cd892f11307202c77/e_d7f264dc.jpg',
                userDescription: 'Сотрудничество'
            },

            etc: false
        },
        {
            publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
            publicName: 'Тысяча чертей, какая',
            publicFollowers: '271 375',
            publicGrowthNum: '1',
            publicGrowthPer: '0.53%',
            user: {
                userId: 1,
                userName: 'Саша Радославов',
                userPhoto: 'http://app.uxpin.com/u/a/0/9/a09e2c748e10258cd892f11307202c77/e_d7f264dc.jpg',
                userDescription: 'Сотрудничество'
            },

            etc: false
        },
        {
            publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
            publicName: 'Эротика',
            publicFollowers: '271 375',
            publicGrowthNum: '2231',
            publicGrowthPer: '0.53%',
            user: {
                userId: 1,
                userName: 'Саша Радославов',
                userPhoto: 'http://app.uxpin.com/u/a/0/9/a09e2c748e10258cd892f11307202c77/e_d7f264dc.jpg',
                userDescription: 'Сотрудничество'
            },

            etc: false
        },
        {
            publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
            publicName: 'Самые красивые девушк',
            publicFollowers: '271 375',
            publicGrowthNum: '1131',
            publicGrowthPer: '0.53%',
            user: {
                userId: 1,
                userName: 'Саша Радославов',
                userPhoto: 'http://app.uxpin.com/u/a/0/9/a09e2c748e10258cd892f11307202c77/e_d7f264dc.jpg',
                userDescription: 'Сотрудничество'
            },

            etc: false
        },
        {
            publicImg: 'http://app.uxpin.com/u/5/4/a/54a4c4259d2169e5bf80dfb906c03cf4/e_68ce31a0.jpg',
            publicName: 'Интересные факты',
            publicFollowers: '221 375',
            publicGrowthNum: '0',
            publicGrowthPer: '0%',
            user: {
                userId: 1,
                userName: 'Саша Радославов',
                userPhoto: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
                userDescription: 'Сотрудничество'
            },

            etc: false
        }
    ]
};

/**
 * Initialization
 */
Events.fire('load_stat', [1, 1, function(data) {
    init(data || Data);
}]);

function init(defData) {
    $(document).ready(function() {
        updateTable(defData);

        $('#filter').keyup(function() {
            var rows = $.grep(defData.rows, function(n) {
                return n.publicName.toLowerCase().indexOf($('#filter').val().toLowerCase()) != -1;
            });
            updateTable({rows: rows});
        });

        $('.growth').click(function() {
            var rows;

            $(this).toggleClass('active');
            if ($(this).hasClass('active')) {
                rows = defData.rows.slice(0).sort(function(first, second) {
                    var a = parseInt(first.publicGrowthNum);
                    var b = parseInt(second.publicGrowthNum);
                    return (a > b) ? -1 : ((a < b) ? 1 : 0);
                });
            } else {
                rows = defData.rows;
            }
            updateTable({rows: rows});
        });

        function updateTable(data) {
            $('#table-body').html(tmpl(TABLE_BODY, data));
        }
    });
}

/**
 * Templating
 */
(function() {
    var t = this;
    var cache = {};
    var format = function(str) {
        return str
            .replace(/[\r\t\n]/g, ' ')
            .split("<?").join("\t")
            .split("'").join("\\'")
            .replace(/\t=(.*?)\?>/g, "',$1,'")
            .split("\t").join("');")
            .split("?>").join("p.push('")
            .split("\r").join("\\'");
    };
    var tmpl = function(str, data) {
        try {
            var fn = (!/[^\w-]/.test(str))
                ? (cache[str] = cache[str] || tmpl($.trim($('#' + str).html() || t[str])))
                : (new Function('obj',
                'var p=[],' +
                    'print=function(){p.push.apply(p,arguments)},' +
                    'isset=function(v){return !!obj[v]},' +
                    'each=function(ui,obj){for(var i=0; i<obj.length; i++) { print(tmpl(ui, $.extend(obj[i],{i:i}))) }};' +
                    "with(obj){p.push('" + format(str) + "');} return p.join('');"
            ));
            return data ? fn(data) : fn;
        }
        catch(e) {
            if (console && console.log) console.log(format(str));
            throw e;
        }
    };

    return t.tmpl = tmpl;
})();

var TABLE_BODY =
'<? each(TABLE_ROW, rows); ?>';

var TABLE_ROW =
'<tr>' +
    '<td>' +
        '<span class="photo">' +
            '<img src="<?=publicImg?>" alt="" />' +
        '</span>' +
        '<?=publicName?>' +
    '</td>' +
    '<td><?=publicFollowers?></td>' +
    '<td>' +
        '<span class="<? print(publicGrowthNum > 0 ? "plus" : "minus"); ?>">' +
            '<?=publicGrowthNum?> <small><?=publicGrowthPer?></small>' +
        '</span>' +
    '</td>' +
    '<td>' +
        '<?=tmpl(CONTACT, user)?>' +
    '</td>' +
'</tr>';

var CONTACT =
'<div class="contact">' +
    '<div class="photo">' +
        '<img src="<?=userPhoto?>" alt="" />' +
    '</div>' +
    '<div class="content">' +
        '<div class="name">' +
            '<a target="_blank" href="http://vk.com/im?sel=<?=userId?>"><?=userName?></a>' +
        '</div>' +
        '<div class="description">' +
            '<?=userDescription?>' +
        '</div>' +
    '</div>' +
    '<div class="actions">' +
        '<span class="action">' +
            '<span class="icon arrow"></span>' +
        '</span>' +
        '<span class="action">' +
            '<span class="icon plus"></span>' +
        '</span>' +
    '</div>' +
'</div>';