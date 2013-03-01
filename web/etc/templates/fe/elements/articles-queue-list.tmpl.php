<?
/** @var $canEditQueue bool */
/** @var $grid array */
/** @var $articlesQueue array */

foreach ($grid as $gridItem) {
    $id = $gridItem['dateTime']->format('U');
    $isEmptyItem = empty($gridItem['queue']);
    if ($isEmptyItem && !$canEditQueue) {
        continue;
    }
    ?>
    <div class="slot
        <?= !$canEditQueue || !empty($gridItem['blocked']) ? 'locked' : '' ?>
        <?= $isEmptyItem ? 'empty' : '' ?>"
         data-id="{$id}"
         data-grid-id="{$gridItem[gridLineId]}"
         data-grid-item-id="{$gridItem[gridLineItemId]}"
         data-start-date="<?= $gridItem['startDate']->format('d.m.Y') ?>"
         data-end-date="<?= $gridItem['endDate']->format('d.m.Y') ?>">
        <? if ($isEmptyItem) { ?>
            <div class="slot-header">
                <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                <span class="datepicker"></span>
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
            $deleteAt = !empty($articleQueue->deleteAt) ? $articleQueue->deleteAt->modify('+1 minute')->defaultTimeFormat() : null;
            ?>
            <? if ($canEditQueue) { ?>
                <div class="slot-header">
                    <span class="time"><?= $gridItem['dateTime']->defaultTimeFormat() ?></span>
                    <span class="datepicker"></span>
                    <span class="time-of-removal"></span>
                    <span class="time-of-remove"><?= $deleteAt ? $deleteAt : '' ?></span>
                    {increal:tmpl://fe/elements/articles-queue-item-header.tmpl.php}
                </div>
            <? } ?>
            <div class="post movable
                <?= !$canEditQueue || !empty($gridItem['blocked']) ? 'blocked' : '' ?>
                <?= !empty($gridItem['failed']) ? 'failed' : '' ?>"
                 data-id="{$articleQueueId}"
                 data-queue-id="{$articleQueueId}">
                <div class="content">
                    {increal:tmpl://fe/elements/articles-queue-item-content.tmpl.php}
                </div>
                <? if (empty($gridItem['blocked']) && $canEditQueue) { ?>
                    <div class="delete"></div>
                <? } ?>
            </div>

            <?
            $author = array();
            if (!empty($articleQueue->articleQueueCreator)) {
                $author = $articleQueue->articleQueueCreator;
            } elseif (!empty($articleQueue->articleAuthor)) {
                $author = $articleQueue->articleAuthor;
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
                </div>
            </div>
        <? } ?>
    </div>
<? } ?>
