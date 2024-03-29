<?  $id = $gridItem['dateTime']->format('U');
    $isEmptyItem = empty($gridItem['queue']);
    $canBeUsed = time() < $gridItem['dateTime']->format('U');
?>
<div class="slot
    <?= !$canEditQueue || !empty($gridItem['blocked']) ? 'locked' : ''?>
    <?= $isEmptyItem ? 'empty' : ''?>
    <?= 'gridLine_' . $gridItem['gridLineId'] ?>
    <?= $gridItem['repeat'] ? 'repeat' : ''?>"
    <?= $canBeUsed ? ' ui-droppable ' : ''?>
     data-id="{$id}"
     data-grid-id="{$gridItem[gridLineId]}"
     data-grid-item-id="{$gridItem[gridLineItemId]}"
     data-start-date="<?= $gridItem['startDate']->format('d.m.Y') ?>"
     data-end-date="<?= $gridItem['endDate']->format('d.m.Y') ?>">
    <? if ($isEmptyItem) { ?>
        <div class="slot-header">
            <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
            <span class="repeater"></span>
            <div class="delete"></div>
        </div>
        <div class="editing-post">
            <div class="textarea-wrap">
                <textarea></textarea>
            </div>
            <div class="attachments"></div>
            <div class="actions">
                <div class="save button">Сохранить</div>
                <div class="cancel button">Отменить</div>
                <div class="upload r">Прикрепить</div>
            </div>
        </div>
    <? } else { ?>
        <?
        $articleQueueId = $gridItem['queue']->articleQueueId;
        $articleRecord = !empty($articleRecords[$articleQueueId]) ? $articleRecords[$articleQueueId] : new ArticleRecord();
        $articleQueue = !empty($articlesQueue[$articleQueueId]) ? $articlesQueue[$articleQueueId] : new ArticleQueue();
        $isRepost = false;
        $originalId = $articleQueue->externalId;
        //отложенный ли пост. если да - у него будем показывать элементы в хедере
        $isPostponed = ( $articleQueue->addedFrom == ArticleUtility::QueueSourceVkPostponed && $articleQueue->startDate > DateTimeWrapper::Now());
        $isInProtectedInterval = (
            $articleQueue->addedFrom == ArticleUtility::QueueSourceSb &&
            $articleQueue->startDate > DateTimeWrapper::Now() &&
            $articleQueue->statusId ==  StatusUtility::Finished
        );

        if ($articleRecord->repostArticleRecordId && isset($repostArticleRecords[$articleRecord->repostArticleRecordId])) {
            $isRepost = true;
            $originalId = $articleRecord->repostExternalId;
            $repostArticleRecord = $repostArticleRecords[$articleRecord->repostArticleRecordId];
        }
        $deleteAt = !empty($articleQueue->deleteAt) ? $articleQueue->deleteAt->modify('+1 minute')->defaultTimeFormat() : null;
        $protectTo = !empty($articleQueue->protectTo) ? $articleQueue->protectTo->modify('+1 minute')->defaultTimeFormat() : null;
        ?>
        <? if ($canEditQueue) { ?>
            <div class="slot-header">
                <? if( $isInProtectedInterval ) {?>
                    <span class="time unlock "><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                <? } else {?>
                    <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                <? } ?>
                <span class="repeater"></span>
                <span class="time-of-removal <?= $deleteAt ? ' visible': '' ?>"></span>
                <span class="time-of-remove "><?= $deleteAt ? $deleteAt : '' ?></span>
                {increal:tmpl://fe/elements/articles-queue-item-header.tmpl.php}
                <? if (empty($gridItem['blocked']) && !$isPostponed ) { ?>
                    <span class="time-of-lock "><?= $protectTo ? $protectTo: '' ?></span>
                    <span class="locked-trigger <?= $protectTo ? ' visible': '' ?>"></span>
                    <span class="edit-trigger "></span>
                    <span class="delete "></span>
                <? } elseif ( $isPostponed ) {?>
                    <span class="time-of-lock unlock "><?= $protectTo ? $protectTo: '' ?></span>
                    <span class="locked-trigger unlock <?= $protectTo ? ' visible': '' ?>"></span>
                <? } ?>
            </div>
        <? } ?>
        <div class="post movable
            <?= !$canEditQueue || !empty($gridItem['blocked']) ? 'blocked' : '' ?>
            <?= !empty($gridItem['failed']) ? 'failed' : '' ?>"
             data-id="<?=  $isEmptyItem ? '' : $gridItem['queue']->articleId ?>"
             data-queue-id="{$articleQueueId}">
            <div class="content">
                {increal:tmpl://fe/elements/articles-queue-item-content.tmpl.php}
                <? if ($isRepost) { ?>
                    {increal:tmpl://fe/elements/articles-queue-item-content-repost.tmpl.php}
                <? } ?>
            </div>
               <span class="original" id="to_post" >
                    <? if ($originalId) { ?>
                        <a href="http://vk.com/wall{$originalId}" target="_blank">Оригинал</a>
                    <? } ?>
                </span>
        </div>

        <?
        if (!empty($articleQueue->articleAuthor)) {
            $author = $articleQueue->articleAuthor;
        } elseif (!empty($articleQueue->articleQueueCreator)) {
            $author = $articleQueue->articleQueueCreator;
        } else {
            $author = new Author();
            $author->avatar = 'http://vk.com/images/camera_c.gif';
        }
        ?>
        <div class="expanded-post">
            <div class="photo">
                <? if ($author->vkId) { ?>
                    <a target="_blank" href="http://vk.com/id{$author->vkId}">
                        <img src="{$author->avatar}" alt="" />
                    </a>
                <? } else { ?>
                    <img src="{$author->avatar}" alt="" />
                <? } ?>
            </div>
            <div class="content">
                <? if ($author->vkId) { ?>
                    <div class="name">
                        <a target="_blank" href="http://vk.com/id{$author->vkId}">
                            <b>{$author->FullName()}</b>
                        </a>
                    </div>
                <? } ?>
                {increal:tmpl://fe/elements/article-item-content.tmpl.php}
                <? if ($isRepost) { ?>
                    {increal:tmpl://fe/elements/articles-queue-item-content-repost.tmpl.php}
                <? } ?>
            </div>
        </div>
    <? } ?>
</div>
