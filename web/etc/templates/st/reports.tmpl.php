{increal:tmpl://st/elements/header.tmpl.php}
<script type="text/javascript" src="/shared/js/st/reports.js"></script>
<link rel="stylesheet" type="text/css" href="/shared/css/st/reports.css" />

<div id="this-is-reporter"></div>
<div id="main" class="main">
    <div class="header">
        <div class="form">
            <input id="public-id" type="text" placeholder="id паблика" />
            <input id="our-public-id" type="text" placeholder="id нашего паблика" />
            <input id="time" type="text" placeholder="Время начала наблюдения" style="width: 200px" />
            <button id="addReport" class="button">+</button>
        </div>
    </div>
    <h3>Результаты</h3>
    <div id="results"></div>
</div>
{increal:tmpl://st/elements/footer.tmpl.php}