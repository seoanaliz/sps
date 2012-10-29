{increal:tmpl://st/elements/header.tmpl.php}
<style type="text/css">
    #main {
        padding: 10px;
    }
    .row {
        border-top: 1px solid #ccc;
        padding: 5px 0;
    }
    .row > div {
        display: inline-block;
        vertical-align: middle;
    }
    .public {
        width: 250px;
    }
    .public > div {
        display: inline-block;
        vertical-align: middle;
    }
    .photo {
        width: 40px;
        height: 40px;
        margin-right: 10px;
        overflow: hidden;
    }
    .photo img {
        max-width: 100%;
    }
    .time {
        width: 90px;
    }
    .name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 160px;
    }
</style>
<script type="text/javascript" src="/shared/js/st/reports.js"></script>

<div id="this-is-reporter"></div>
<div id="main" class="main">
    <div class="form">
        <input id="public-id" type="text" placeholder="id паблика" />
        <input id="our-public-id" type="text" placeholder="id нашего паблика" />
        <input id="time" type="text" placeholder="Время начала наблюдения" style="width: 200px" />
        <button id="addReport" class="button">+</button>
    </div>
    <h3>Результаты</h3>
    <div id="results">...</div>
</div>
{increal:tmpl://st/elements/footer.tmpl.php}