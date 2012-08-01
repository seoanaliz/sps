<?
    /** @var $comment Comment */
    if (!empty($comment)) {
        $commentAuthor = !empty($comment->authorId) ? $comment->author : $comment->editor;
?>
<div class="comment" data-id="{$comment->commentId}">
    <? if (!empty($comment->authorId) && $comment->authorId == $__Author->authorId) { ?>
        <div class="delete"></div>
    <? } ?>
    <div class="photo">
        <a target="_blank" href="http://vk.com/id{$commentAuthor->vkId}">
            <img src="{$commentAuthor->avatar}" alt="" />
        </a>
    </div>
    <div class="content">
        <div class="title">
            <a target="_blank" href="http://vk.com/id{$__Author->vkId}">{$commentAuthor->FullName()}</a>
        </div>
        <div class="text"><?= nl2br(HtmlHelper::RenderToForm($comment->text)) ?></div>
        <span class="date">{$comment->createdAt->defaultFormat()}</span>
    </div>
</div>
<? } ?>