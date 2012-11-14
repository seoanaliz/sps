{increal:tmpl://st/elements/header.tmpl.php}
<script type="text/javascript" xmlns="http://www.w3.org/1999/html" src="/shared/js/st/reports.js"></script>
<link rel="stylesheet" type="text/css" href="/shared/css/st/reports.css" />

<div id="this-is-reporter"></div>
<div id="main" class="main">
    <div class="header">
        <div class="form">
            <input id="public-id" type="text" placeholder="Кого рекламируем" />
            <input id="our-public-id" type="text" placeholder="Где размещаем" />
            <input id="time" type="text" placeholder="Время начала наблюдения" style="width: 200px" />
            <button id="addReport" class="button">+</button>
        </div>
    </div>
    <div class="list-head clear-fix">
        <div class="item our-public">Кого рекламируем<span class="icon arrow"></div>
        <div class="item partner">Где размещаем<span class="icon arrow"></div>
        <div class="item post-time">вромя поста<span class="icon arrow"></div>
        <div class="item delete-time">время удаления<span class="icon arrow"></div>
        <div class="item visitors">посетители<span class="icon arrow"></div>
        <div class="item subscribers">подписалось<span class="icon arrow"></div>
    </div>
    <div id="results"></div>
</div>
{increal:tmpl://st/elements/footer.tmpl.php}