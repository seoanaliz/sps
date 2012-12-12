<?
/**
 * @var $RoleUtility RoleAccessUtility
 * @var $sourceTypes array
 * @var $gridTypes array
 *
 */
?>
{increal:tmpl://fe/elements/header.tmpl.php}
<script src="{web:js://fe/main.js}" type="text/javascript"></script>
<div id="go-to-top">Наверх</div>
<div class="layer clear-fix">
    <div class="left-panel">
        <div class="block">
            <div class="header">

                <div id="wall-load"></div>

                <div class="type-selector">
                    <? $i=0;
                    foreach($sourceTypes as $sourceType => $sourceTypeTitle):
                    ?>
                       <a class="sourceType <?=($i == 0 ? 'active' : '')?>" data-type="{$sourceType}"
                          id="sourceType-<?=$sourceType?>">{$sourceTypeTitle}</a>
                    <?
                    $i++;
                    endforeach;
                    ?>
                    <a data-type="authors-list">+</a>
                </div>

                <select multiple="multiple" id="source-select">
                    <?
                    foreach ($sourceFeeds as $sourceFeed) {
                        ?><option value="{$sourceFeed.sourceFeedId}">{$sourceFeed.title}</option><?
                    }
                    ?>
                </select>

                <!--div class="controls">
                    <div class="ctl spr gear"></div>
                    <div class="ctl spr plus"></div>
                    <div class="ctl spr del"></div>
                </div -->

                <div style="position: absolute; top: 48px; right: 18px; width: 300px;" id="slider-cont">
                    <div id="slider-range"></div>
                </div>
            </div>

            <div class="wall-title">
                <span class="count">&nbsp;</span>
                <span class="filter" id="filter-list">
                    <a data-type="new">новые записи</a>
                </span>
            </div>
            {increal:tmpl://fe/elements/new-post-form.tmpl.php}

            <div class="wall" id="wall">

            </div>

            <div id="wallloadmore" class="hidden">Больше</div>
        </div>
    </div>

    <div class="right-panel">
        <div class="block">
            <div class="header bb">

                <div class="user-info">
                    <span class="user-photo">
                        <a target="_blank">
                            <img width="22px" src="" alt="" />
                        </a>
                    </span>
                    <span class="user-name">
                        <a target="_blank"></a>
                    </span>
                    <span class="logout">
                        <a href="/login/">Выход</a>
                    </span>
<!--                    <span class="counter">11000</span>-->
<!--                    <span class="counter">68%</span>-->
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
                                <?
                                $json = array();
                                foreach ($targetFeeds as $targetFeed) {
                                    array_push($json, array(
                                        'id' => $targetFeed->targetFeedId,
                                        'title' => $targetFeed->title,
                                        'icon' => $targetInfo[$targetFeed->targetFeedId]['img'],
                                        'isActive' => ($targetFeed->targetFeedId == $currentTargetFeedId),
                                    ));
                                }
                                echo 'var rightPanelData = '.json_encode($json);
                                ?>
                        </script>
                    </div>

                    <!--div class="controls">
                       <div class="ctl spr gear"></div>
                       <div class="ctl spr plus"></div>
                       <div class="ctl spr del"></div>
                   </div -->

                    <div class="type-selector">
                        <a class="grid_type all" <?=count($gridTypes) < 2 ? 'style="display:none"' : ''?> data-type="<?= GridLineUtility::TYPE_ALL ?>">Все записи</a>
                        <?
                        $i=0;
                        foreach ($gridTypes as $type => $name): ?>
                        <a class="grid_type <?=!$i++ ? 'active' : ''?>" data-type="<?= $type ?>"><?=$name?></a>
                        <? endforeach; ?>
                    </div>

                </div>
            </div>

            <div class="queue-title">&nbsp;</div>
            <div class="items drop" id="queue" style="display: none;"></div>
            <div class="queue-footer">
                <a class="add-button">Добавить ячейку</a>
            </div>
        </div>
    </div>
</div>
{increal:tmpl://fe/elements/footer.tmpl.php}