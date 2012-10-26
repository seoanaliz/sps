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
        width: 150px;
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
        width: 50px;
    }
</style>
<div id="main" class="main">
    <div class="form">
        <input type="text" placeholder="id паблика" />
        <input type="text" placeholder="id нашего паблика" />
        <input type="text" placeholder="Время" />
        <button class="button">+</button>
    </div>
    <h3>Результаты</h3>
    <div class="results">
        <div class="row">
            <div class="public">
                <div class="photo">
                    <img src="http://cs411822.userapi.com/g40391948/e_46daee48.jpg" alt="" />
                </div>
                <div class="name">
                    <a href="http://vk.com">Public</a>
                </div>
            </div>
            <div class="public">
                <div class="photo">
                    <img src="http://cs421416.userapi.com/g43888952/d_1d0fc9c4.jpg" alt="" />
                </div>
                <div class="name">
                    <a href="http://vk.com">Public</a>
                </div>
            </div>
            <div class="time">15:30</div>
            <div class="time">15:58</div>
        </div>
    </div>
</div>
{increal:tmpl://st/elements/footer.tmpl.php}