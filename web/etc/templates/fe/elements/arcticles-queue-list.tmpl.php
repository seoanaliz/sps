<?
    foreach ($grid as $gridItem) {
        $id = $gridItem['dateTime']->format('U');
        if (empty($gridItem['queue'])) {
            ?>
                <div class="slot <?= empty($gridItem['blocked']) ? 'empty' : '' ?>" data-id="{$id}">
                    <div class="slot-header">
                        <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                        <span class="datepicker"></span>
                    </div>
                </div>
            <?
        } else {
            $articleQueueId = $gridItem['queue']->articleQueueId;
            $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
            ?>
                <div class="slot <?= !empty($gridItem['blocked']) ? 'locked' : '' ?>" data-id="{$id}">
                    <div class="slot-header">
                        <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                        <span class="datepicker"></span>
                        <? if (!empty($articleRecord->link)) { ?>
                            <span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>
                        <? } ?>
                        <? if (UrlParser::IsContentWithLink($articleRecord->content)) { ?>
                            <span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>
                        <? } ?>
                        <? if (UrlParser::IsContentWithHash($articleRecord->content)) { ?>
                            <span class="hash-span" title="Пост с хештэгом">#hash</span>
                        <? } ?>
                    </div>
                    <div class="post movable <?= !empty($gridItem['blocked']) ? 'blocked' : '' ?> <?= !empty($gridItem['failed']) ? 'failed' : '' ?>" data-id="{$articleQueueId}" data-queue-id="{$articleQueueId}">
                        <div class="content">
                            <?
                                $content = nl2br(HtmlHelper::RenderToForm($articleRecord->content));
                                $collapsed = (strlen($content) > 50) ? 'collapsed' : false;
                            ?>
                            <div class="text {$collapsed}">
                                {$content}
                                <? if ($collapsed) { ?>
                                    <span class="toggle-text"></span>
                                <? } ?>
                            </div>
                            <? if (!empty($articleRecord->link)) { ?>
                                <div class="link-info-content">
                                    <div class="link-description-content">
                                        <img src="{web:images://fe/ajax-loader.gif}" alt="" class="<?= ($extLinkLoader) ? 'ajax-loader-ext' : 'ajax-loader' ?>" rel="{form:$articleRecord->link}" />
                                    </div>
                                </div>
                            <? } ?>
                            <? if (!empty($articleRecord->photos)) { ?>
                                <div class="images">
                                    <? $i = 0; ?>
                                    <? foreach($articleRecord->photos as $photoItem) {
                                        $i++;
                                        //$size = ($i == 1) ? 'original' : 'small';
                                        $size = 'original';
                                    ?>
                                        <div class="img">
                                            <img src="<?= MediaUtility::GetFilePath( 'Article', 'photos', $size, $photoItem['filename'], MediaServerManager::$MainLocation) ?>">
                                        </div>
                                    <? } ?>
                                </div>
                            <? } ?>
                        </div>
                        <? if(empty($gridItem['blocked'])) {?>
                            <div class="spr delete"></div>
                        <? } ?>
                    </div>
                </div>
            <?
        }
    }
?>