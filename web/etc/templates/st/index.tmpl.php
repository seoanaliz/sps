{increal:tmpl://st/elements/header.tmpl.php}
<div id="global-loader"></div>
<div id="main" class="main">
    <div class="header">
        <div class="tab-bar"></div>
    </div>
    <table>
        <tbody>
            <tr>
                <td class="left-column">
                    <div class="content">
                        <div class="table" id="table"></div>
                        <div id="load-more-table">Показать больше</div>
                    </div>
                </td>
                <td class="right-column">
                    <div class="filter">
                        <div class="title">Аудитория</div>
                        <div class="audience">
                            <div class="slider-range clear-fix">
                                <div class="value-min"></div>
                                -
                                <div class="value-max"></div>
                            </div>
                            <div class="slider-wrap"></div>
                        </div>
                        <div class="title">Период</div>
                        <div class="period">
                            <label><input name="period" type="radio" value="day" checked="checked"><span>День</span></label>
                            <label><input name="period" type="radio" value="week"><span>Неделя</span></label>
                            <label><input name="period" type="radio" value="month"><span>Месяц</span></label>
                        </div>
                        <div class="list">
                            <div class="item selected">List1</div>
                            <div class="item">List1</div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <div id="go-to-top">Наверх</div>
</div>
{increal:tmpl://st/elements/footer.tmpl.php}