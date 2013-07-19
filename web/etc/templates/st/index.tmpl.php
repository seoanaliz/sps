<?php
/**
*@var $canSuggestGlobalGroups boolean
*@var $hasAccessToPrivateGroups boolean
*@var $canEditGlobalGroups boolean
*/
?>

{increal:tmpl://st/elements/header.tmpl.php}
<div id="global-loader"></div>
<div id="main" class="main">
    <div class="header clear-fix">
        <div class="tab-bar"></div>
        <div class="login-info"></div>
        <div class="button-wrap">
            <script type="text/javascript"><!--
                document.write(VK.Share.button('http://socialboard.ru/stat/?from=share', {type: "button", text: "Поделиться ссылкой"}));
            --></script>
        </div>
        <div class="under">
            <? if ($canEditGlobalGroups) {?>
                <div class="actions">
                    <a class="share">Поделиться</a>
                    <a class="delete">Удалить</a>
                </div>
            <? } ?>
        </div>
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
                        <div class="audience-wrapper">
                            <div class="title">Аудитория</div>
                            <div class="audience">
                                <div class="slider-range clear-fix">
                                    <div class="value-min"></div>
                                    -
                                    <div class="value-max"></div>
                                </div>
                                <div class="slider-wrap"></div>
                            </div>
                        </div>
                        <div class="period-wrapper">
                            <div class="title">Период</div>
                            <div class="period">
                                <label><input name="period" type="radio" value="day" checked="checked"><span>День</span></label>
                                <label><input name="period" type="radio" value="week"><span>Неделя</span></label>
                                <label><input name="period" type="radio" value="month"><span>Месяц</span></label>
                            </div>
                        </div>
                        <div class="list buttons">
                            <div class="item selected" data-id="all">Все паблики</div>    
                            <? if ($canEditGlobalGroups) {?>
                                <div class="item editor_lists" data-id="all_not_listed">Не в группе</div>
                            <? } ?>
                        </div>
                        <? if ($hasAccessToPrivateGroups) {?>
                            <div class="title">Личные</div>
                            <div class="list private editor_lists">
                            </div>
                        <? } ?>
                        <div class="title">Категории</div>
                        <div class="list global">
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div id="go-to-top">Наверх</div>
</div>

{increal:tmpl://st/elements/footer.tmpl.php}
