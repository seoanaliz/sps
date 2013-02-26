<?
/**
 * @var $sourceTypes array
 * @var $gridTypes array
 * @var $availableSourceTypes array
 * @var $articleStatuses array
 * @var $availableArticleStatuses array
 */
?>
{increal:tmpl://fe/elements/header.tmpl.php}
<script src="{web:js://fe/main.js}" type="text/javascript"></script>
<div id="go-to-top">Наверх</div>
<div class="layer clear-fix">
    <div id="left-panel" class="left-panel">
        <div class="block">
            <div class="header">

                <div id="wall-load"></div>

                <div class="type-selector">
                    <? $isFirst=0;
                    foreach($sourceTypes as $sourceType => $sourceTypeTitle):
                        ?>
                        <a class="sourceType <?=($isFirst == 0 ? 'active' : '')?>" data-type="{$sourceType}"
                            <?=!in_array($sourceType, $availableSourceTypes) ? 'style="display:none"' : ''?>
                           id="sourceType-<?=$sourceType?>"><?=$sourceTypeTitle?></a>
                        <?
                        $isFirst++;
                    endforeach;
                    ?>
                </div>

                <select multiple="multiple" id="source-select" data-classes="hidden" style="display: none;">
                    <?
                    foreach ($sourceFeeds as $sourceFeed) {
                        ?><option value="{$sourceFeed.sourceFeedId}">{$sourceFeed.title}</option><?
                    }
                    ?>
                </select>

                <div id="slider-cont" class="clear-fix">
                    <div id="slider-range"></div>
                </div>
                <div class="clear-fix"></div>

                <div class="user-groups-tabs tab-bar no-padding hidden">
                </div>

                <div class="authors-tabs tab-bar no-padding">
                    <?
                    $isFirst = true;
                    foreach ($articleStatuses as $articleStatus => $statusName) :
                        $isHidden = !in_array($articleStatus, $availableArticleStatuses);
                        ?>
                        <div class="authors-tab-new tab<?=($isFirst && !$isHidden) ? ' selected' : ''?>" <?=$isHidden ? 'style="display:none"' : ''?>  data-mode="my" data-article-status="<?=$articleStatus?>"><?=$statusName?></div>
                        <?
                        if (!$isHidden){
                            $isFirst = false;
                        }
                    endforeach ?>
                </div>
            </div>

            <div class="wall-title">
                <span class="count">&nbsp;</span>
                <span class="filter" id="filter-list">
                    <a data-type="new">новые записи</a>
                </span>
                <span class="wall-switcher" id="wall-switcher">
                    <a data-type="deferred" data-switch-to="approved">к одобренным записям</a>
                    <a data-type="approved" data-switch-to="deferred">к записям на рассмотрении</a>
                    <a data-type="my" data-switch-to="all">ко всем записям</a>
                    <a data-type="all" data-switch-to="my">к моим записям</a>
                </span>
            </div>
            {increal:tmpl://fe/elements/new-post-form.tmpl.php}

            <div class="wall" id="wall"></div>
        </div>
    </div>

    <div id="right-panel-background" class="right-panel-background"></div>
    <div id="right-panel" class="right-panel">
        <div id="right-panel-expander" class="right-panel-expander">
            <div class="arrow"></div>
        </div>
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
                </div>

                <div class="filter">
                    <div class="calendar">
                        <div class="prev"></div>
                        <input type="text" id="calendar" value="<?= $currentDate->DefaultDateFormat() ?>"/>
                        <input type="text" id="calendar-fix"/>
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

                    <div class="type-selector">
                        <a class="grid_type all" data-type="<?= GridLineUtility::TYPE_ALL ?>">Все записи</a>
                        <?
                        $isFirst=0;
                        foreach ($gridTypes as $type => $name): ?>
                            <a class="grid_type <?=!$isFirst++ ? 'active' : ''?>" data-type="<?= $type ?>"><?=$name?></a>
                            <? endforeach; ?>
                    </div>

                </div>
            </div>

            <div class="queue-title">&nbsp;</div>
            <div class="items drop" id="queue"></div>
            <div class="queue-footer">
                <a class="add-button">Добавить ячейку</a>
            </div>
        </div>
    </div>
</div>
{increal:tmpl://fe/elements/footer.tmpl.php}
