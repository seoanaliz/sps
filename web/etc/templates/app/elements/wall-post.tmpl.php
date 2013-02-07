<?
    /** @var $article Article */
    /** @var $articleRecord ArticleRecord */
    /** @var $author Author */
    /** @var $targetFeed TargetFeed */
    /** @var $targetInfo array */
    /** @var $isWebUserEditor bool */

    if (!empty($article)) {
        $asNew = '';
        $newPostType = '';
        if (!empty($authorEvents[$article->articleId]) && !empty($__Author) && $article->authorId == $__Author->authorId) {
            if ($authorEvents[$article->articleId]->isQueued && $tabType == 'queued') {
                $asNew = 'new';
                $newPostType = 'queued';
            } else if ($authorEvents[$article->articleId]->isSent && $tabType == 'sent') {
                $asNew = 'new';
                $newPostType = 'sent';
            }
        }
        $hasComments = !empty($commentsData[$article->articleId]);
        $showDelete = false;
        if ($isWebUserEditor) {
            $showDelete = $author->authorId == $__Author->authorId;
        } elseif ($article->articleStatus == Article::STATUS_REVIEW) {
            $showDelete = true;
        }
?>

<div class="post <?= !$hasComments ? 'no-comments' : '' ?>" data-id="{$article->articleId}">
    <? if ($showDelete) { ?>
        <div class="delete"></div>
    <? } ?>
    <? if (!empty($author)) { ?>
    <div class="photo">
        <a target="_blank" href="http://vk.com/id{$author->vkId}">
            <img src="{$author->avatar}" alt="" />
        </a>
    </div>
    <? } ?>
    <div class="content">
        <div class="hight-light {$asNew}" data-type="{$newPostType}">
            <? if (!empty($author)) { ?>
                <div class="title">
                    <a target="_blank" href="http://vk.com/id{$author->vkId}">{$author->FullName()}</a>
                </div>
            <? } ?>
            <div class="text">
                <?
                $content = $articleRecord->content;
                $contentPart = mb_substr($content, 0, 300);
                $contentPart = ($contentPart != $content) ? $contentPart . '...' : '';
                ?>
                <div class="shortcut">
                    <? if ($contentPart) { ?>
                        <?= nl2br(HtmlHelper::RenderToForm($contentPart)) ?>
                        <a href="javascript:;" class="show-cut">Показать полностью...</a>
                    <? } else { ?>
                        <?= nl2br(HtmlHelper::RenderToForm($content)) ?>
                    <? } ?>
                </div>
                <? if ($contentPart) { ?>
                <div class="cut"><?= nl2br(HtmlHelper::RenderToForm($content)) ?></div>
                <? } ?>
            </div>

            <? if (!empty($articleRecord->photos)) { ?>
                <div class="attachments">
                <? foreach($articleRecord->photos as $photoItem) {
                    $path = MediaUtility::GetArticlePhoto($photoItem);
                    ?><img src="{$path}" alt="" /><?
                } ?>
                </div>
            <? } ?>

            <div class="sign clear-fix">
                <div class="user-info">
                    <? if (false && !empty($author)) { ?>
                    <span class="photo">
                        <a target="_blank" href="http://vk.com/id{$author->vkId}">
                            <img src="{$author->avatar}" alt="" />
                        </a>
                    </span>
                    <span class="name">
                        <a target="_blank" href="http://vk.com/id{$author->vkId}">{$author->FullName()}</a>
                    </span>
                    <? } ?>
                    <? if ($tabType == 'sent' && !empty($article->sentAt)) { ?>
                        Отправлено <span class="date">{$article->sentAt->defaultFormat()}</span>
                    <? } else if ($tabType == 'queued' && !empty($article->queuedAt)) { ?>
                        Запланировано <span class="date">{$article->queuedAt->defaultFormat()}</span>
                    <? } else { ?>
                        <span class="date">{$article->createdAt->defaultFormat()}</span>
                    <? } ?>
                    <? if (empty($commentsData[$article->articleId])) { ?>
                        | <a class="action show-new-comment" href="javascript:;">Комментировать</a>
                    <? } ?>
                </div>
            </div>
        </div>
        {increal:tmpl://app/elements/wall-comments.tmpl.php}
    </div>
</div>
<? } ?>
