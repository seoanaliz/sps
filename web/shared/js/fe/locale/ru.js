var Lang = {
    articleQueueExists: 'Данный пост уже отправлялся в эту ленту'
    , emptySourceFeedId: 'Не выбран источник'
    , emptyArticle: 'Введите текст, ссылку или загрузите хотя бы одну фотографию'
    , saveError: 'Ошибка сохранения'
    , 'Time between posts is too small': 'Слишком маленький интервал между постами'
    , 'Too many posts this day': 'Слишком много постов на этот день'
    , 'Too many posts this hour': 'Слишком много постов в час'
    , 'Too many posts by this user in 7 hours': 'Слишком много постов с этого аккаунта Vk'
    , declOfNum: function (num, titles) {
        var number = Math.abs(num);
        var cases = [2, 0, 1, 1, 1, 2];
        return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
    }
};