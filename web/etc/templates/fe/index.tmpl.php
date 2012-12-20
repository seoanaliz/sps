<?
/**
 * @var $SourceAccessUtility SourceAccessUtility
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
    <div class="left-panel">
        <div class="block">
            <div class="header">

                <div id="wall-load"></div>

                <div class="type-selector">
                    <? $isFirst=0;
                    foreach($sourceTypes as $sourceType => $sourceTypeTitle):
                    ?>
                       <a class="sourceType <?=($isFirst == 0 ? 'active' : '')?>" data-type="{$sourceType}"
                          <?=!in_array($sourceType, $availableSourceTypes) ? 'style="display:none"' : ''?>
                          id="sourceType-<?=$sourceType?>">{$sourceTypeTitle}</a>
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

                <div style="position: absolute; top: 48px; right: 18px; width: 300px;" id="slider-cont">
                    <div id="slider-range"></div>
                </div>

                <div class="user-groups-tabs hidden">
                    <div class="add-user-group-button">+</div>
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
                        <a class="grid_type all" <?=count($gridTypes) < 2 ? 'style="display:none"' : ''?> data-type="<?= GridLineUtility::TYPE_ALL ?>">Все записи</a>
                        <?
                        $isFirst=0;
                        foreach ($gridTypes as $type => $name): ?>
                        <a class="grid_type <?=!$isFirst++ ? 'active' : ''?>" data-type="<?= $type ?>"><?=$name?></a>
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
