{increal:tmpl://fe/elements/header.tmpl.php}
<div id="go-to-top">Наверх</div>
<div class="layer">
    <div class="left-panel">
        <div class="block">
            <div class="header">

                <div id="wall-load"></div>

                <div class="type-selector">
                    <? foreach(SourceFeedUtility::$Types as $sourceType => $sourceTypeTitle) { ?>
                    <a href="#" class="<?= ($sourceType == $currentSourceType) ? 'active' : '' ?>" data-type="{$sourceType}">{$sourceTypeTitle}</a>
                    <? } ?>
                </div>

                <select multiple="multiple" id="source-select">
                    <?
                        foreach ($sourceFeeds as $sourceFeed) {
                            ?><option value="{$sourceFeed.sourceFeedId}" <?= (in_array($sourceFeed->sourceFeedId, $currentSourceFeedIds)) ? 'selected="selected"' : '' ?>>{$sourceFeed.title}</option><?
                        }
                    ?>
                </select>

                <!--div class="controls">
                    <div class="ctl spr gear"></div>
                    <div class="ctl spr plus"></div>
                    <div class="ctl spr del"></div>
                </div -->

                <div style="position: absolute; top: 48px; right: 18px; width: 300px; <?= ($currentSourceType == SourceFeedUtility::Ads) ? 'display: none;' : '' ?>" id="slider-cont">
                    <div id="slider-range"></div>
                </div>
            </div>

            <div class="wall-title">
                114 записей
                <span class="filter">
                    <a href="#">новые записи</a>
                </span>
            </div>

            {increal:tmpl://fe/elements/new-post-form.tmpl.php}
            <div class="wall" id="wall"></div>

            <div id="wallloadmore" class="hidden">Больше</div>
        </div>
    </div>

    <div class="right-panel">
        <div class="block">
            <div class="header bb">

                <div class="user-info">
                    <img width="22px" src="" alt="" />
                    Вася Пупскин

                    <span class="counter">
                        11000
                    </span>
                    <span class="counter">
                        68%
                    </span>
                </div>

                <div class="filter">
                    <div class="calendar">
                        <div class="prev"></div>
                        <input type="text" id="calendar" value="<?= $currentDate->DefaultDateFormat() ?>"/>
                        <div class="next"></div>
                        <div class="caption default">Дата</div>
                        <div class="tip"></div>
                    </div>

                    <div id="right-drop-down" class="drop-down right-drop-down">
                        <div class="caption default">Паблик</div>
                        <div class="tip"><s></s></div>
                        <div class="icon"></div>
                        <script type="text/javascript">
                            (function() {
                                <?
                                $json = array();
                                foreach ($targetFeeds as $targetFeed) {
                                    array_push($json, array(
                                        'id' => $targetFeed->targetFeedId,
                                        'title' => $targetFeed->title,
                                        'icon' => 'http://cs10308.userapi.com/u4718705/e_be62b8f2.jpg',
                                        'isActive' => ($targetFeed->targetFeedId == $currentTargetFeedId),
                                    ));
                                }
                                echo 'var rightPanelData = '.json_encode($json);
                                ?>

                                $("#right-drop-down").dropdown({
                                    data: rightPanelData,
                                    type: 'checkbox',
                                    addClass: 'right',
                                    onchange: function(item) {
                                        $(this)
                                            .data('selected', item.id)
                                            .find('.caption').text(item.title);
                                        if (item.icon) {
                                            var icon = $(this).find('.icon img');
                                            if (!icon.length) {
                                                icon = $('<img src="' + item.icon + '"/>').appendTo($(this).find('.icon'))
                                            }
                                            icon.attr('src', item.icon);
                                        }
                                        Events.fire('rightcolumn_dropdown_change', []);
                                    },
                                    oncreate: function() {
                                        $(this).find('.default').removeClass('default');
                                        $("#right-drop-down").data('menu').find('.ui-dropdown-menu-item.active').mouseup();
                                    }
                                });
                            })();
                        </script>
                    </div>

                    <!--div class="controls">
                       <div class="ctl spr gear"></div>
                       <div class="ctl spr plus"></div>
                       <div class="ctl spr del"></div>
                   </div -->

                    <div class="type-selector">
                        <a href="#" class="active" data-type="all">Все записи</a>
                        <a href="#" class="" data-type="content">Контент <span class="counter">8</span></a>
                        <a href="#" class="" data-type="ads2">Реклама</a>
                    </div>
                </div>

            </div>

            <div class="items drop" id="queue" style="display: none;">
            </div>
        </div>
    </div>
</div>
{increal:tmpl://fe/elements/footer.tmpl.php}