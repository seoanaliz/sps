<?
/**
 * @var int $authorId - ид автора текущего пользователя
 */
/** @var $comment Comment */
    if (!empty($comment)) {
        $commentAuthor = !empty($comment->authorId) ? $comment->author : $comment->editor;
        $isEditor = !empty($comment->authorId) ? false : true;

        $showDelete = false;
        if (!empty($comment->authorId) && $comment->authorId == $authorId) {
            $showDelete = true;
        } else if (!empty($__Editor)) {
            $showDelete = true;
        }

        $asNew = '';
        if (!empty($article) && !empty($authorEvents[$article->articleId]) && !empty($__Author) && $article->authorId == $__Author->authorId) {
            if (!empty($authorEvents[$article->articleId]->commentIds) && in_array($comment->commentId, $authorEvents[$article->articleId]->commentIds)) {
                $asNew = 'new';
            }
        }
?>
<div class="comment {$asNew}" data-id="{$comment->commentId}">
    <? if ($showDelete) { ?>
        <div class="delete"></div>
    <? } ?>
    <div class="photo">
        <a target="_blank" href="http://vk.com/id{$commentAuthor->vkId}">
            <img src="{$commentAuthor->avatar}" alt="" />
        </a>
    </div>
    <div class="content">
        <div class="title">
            <? if (!empty($commentAuthor)) { ?>
                <a target="_blank" href="http://vk.com/id{$commentAuthor->vkId}">{$commentAuthor->FullName()}</a>
            <? } ?>
            <? if ($isEditor) { ?>
                <span class="mark-editor">— редактор</span>
            <? } ?>
        </div>
        <div class="text">
            <?
            $content = $comment->text;
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

        <span class="date">{$comment->createdAt->defaultFormat()}</span>
    </div>
</div>
<? } ?>