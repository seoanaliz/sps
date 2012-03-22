<?
    foreach ($grid as $gridItem) {
        $id = $gridItem['dateTime']->format('U');
        if (empty($gridItem['queue'])) {
            ?>
                <div class="slot empty" data-id="{$id}">
                    <div class="time"><?= $gridItem['dateTime']->defaultFormat() ?></div>
                    <div class="content"></div>
                </div>
            <?
        } else {
            $articleQueueId = $gridItem['queue']->articleQueueId;
            $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
            ?>
                <div class="slot" data-id="{$id}">
                    <div class="time"><?= $gridItem['dateTime']->defaultFormat() ?></div>
                    <div class="post <?= !empty($gridItem['blocked']) ? 'blocked' : '' ?>" data-id="{$articleQueueId}" data-queue-id="{$articleQueueId}">
                        <div class="content">
                            <?= nl2br($articleRecord->content) ?>
                            <? foreach($articleRecord->photos as $photoItem) { ?>
                            <br /><img src="<?= MediaUtility::GetFilePath( 'Article', 'photos', 'small', $photoItem['filename'], MediaServerManager::$MainLocation) ?>">
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