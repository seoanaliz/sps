var Lang = {
    articleQueueExists: 'Данный пост уже отправлялся в эту ленту'
    , emptySourceFeedId: 'Не выбран источник'
    , emptyArticle: 'Введите текст, ссылку или загрузите хотя бы одну фотографию'
    , saveError: 'Ошибка сохранения'
    , declOfNum: function (number, titles) {
        cases = [2, 0, 1, 1, 1, 2];
        return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
    }
};