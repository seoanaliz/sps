<?
    foreach ($grid as $gridItem) {
        $id = $gridItem['dateTime']->format('U');
        if (empty($gridItem['queue'])) {
            ?>
                <div class="slot <?= empty($gridItem['blocked']) ? 'empty' : '' ?>" data-id="{$id}">
                    <div class="time"><?= $gridItem['dateTime']->defaultFormat() ?></div>
                    <div class="content"></div>
                </div>
            <?
        } else {
            $articleQueueId = $gridItem['queue']->articleQueueId;
            $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
            ?>
                <div class="slot <?= !empty($gridItem['blocked']) ? 'locked' : '' ?>" data-id="{$id}">
                    <div class="time">
                        <?= $gridItem['dateTime']->defaultFormat() ?>
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
                    <div class="post <?= !empty($gridItem['blocked']) ? 'blocked' : '' ?>" data-id="{$articleQueueId}" data-queue-id="{$articleQueueId}">
                        <div class="content">
                            <?
                                $content = nl2br(HtmlHelper::RenderToForm($articleRecord->content));
                            ?>
                            {$content}

                            <? if (!empty($articleRecord->photos)) { ?>
                                <? foreach($articleRecord->photos as $photoItem) { ?>
                                <br /><img src="<?= MediaUtility::GetFilePath( 'Article', 'photos', 'small', $photoItem['filename'], MediaServerManager::$MainLocation) ?>">
                                <? } ?>
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