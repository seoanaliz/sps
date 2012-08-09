<?
    /** @var $comment Comment */
    if (!empty($comment)) {
        $commentAuthor = !empty($comment->authorId) ? $comment->author : $comment->editor;

        $showDelete = false;
        if (!empty($__Author) && !empty($comment->authorId) && $comment->authorId == $__Author->authorId) {
            $showDelete = true;
        } else if (!empty($__Editor)) {
            $showDelete = true;
        }
        $asNew = ($comment->commentId % 2) ? 'new' : '';
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
            <a target="_blank" href="http://vk.com/id{$commentAuthor->vkId}">{$commentAuthor->FullName()}</a>
        </div>
        <div class="text"><?= nl2br(HtmlHelper::RenderToForm($comment->text)) ?></div>
        <span class="date">{$comment->createdAt->defaultFormat()}</span>
    </div>
</div>
<? } ?>