{increal:tmpl://fe/elements/header.tmpl.php}
<div class="layer">
    <div class="left-panel">
        <div class="block">
            <div class="header bb">

                <div class="drop-down">
                    <div class="caption default">Источник</div>
                    <div class="tip"><s></s></div>
                    <ul>
                        <?
                            foreach ($sourceFeeds as $sourceFeed) {
                                ?><li data-id="{$sourceFeed.sourceFeedId}">{$sourceFeed.title}</li><?
                            }
                        ?>
                    </ul>
                </div>

                <!--div class="controls">
                    <div class="ctl spr gear"></div>
                    <div class="ctl spr plus"></div>
                    <div class="ctl spr del"></div>
                </div -->

            </div>
            <div class="newpost collapsed bb hidden">
                <div class="input" contenteditable="true"></div>
                <div class="tip">Есть чем поделиться?</div>
                <div class="save button spr l">Сохранить</div>
                <div class="attach button spr r">Прикрепить</div>
                <div class="clear"></div>
            </div>

            <div class="wall" id="wall">

            </div>

            <div id="wallloadmore" class="hidden">Больше</div>
        </div>
    </div>

    <div class="right-panel">
        <div class="block">
            <div class="header bb">

                <div class="calendar">
                    <input type="text" id="calendar"/>
                    <div class="caption default">Дата</div>
                    <div class="tip"><b>cal</b></div>
                </div>

                <div class="drop-down">
                    <div class="caption default">Паблик</div>
                    <div class="tip"><s></s></div>
                    <ul>
                        <?
                        foreach ($targetFeeds as $targetFeed) {
                            ?><li data-id="{$targetFeed.targetFeedId}">{$targetFeed.title}</li><?
                        }
                        ?>
                    </ul>
                </div>

                <!--div class="controls">
                    <div class="ctl spr gear"></div>
                    <div class="ctl spr plus"></div>
                    <div class="ctl spr del"></div>
                </div -->

            </div>

            <div class="items block drop" id="queue" style="display: none;">
            </div>
        </div>
    </div>
</div>
{increal:tmpl://fe/elements/footer.tmpl.php}