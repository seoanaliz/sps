<?
    /** @var $article Article */
    /** @var $articleRecord ArticleRecord */
    /** @var $author Author */
    /** @var $targetFeed TargetFeed */
    /** @var $targetInfo array */

    if (!empty($article)) {
        $asNew = '';
        if (!empty($authorEvents[$article->articleId]) && !empty($__Author) && $article->authorId = $__Author->authorId) {
            if ($authorEvents[$article->articleId]->isSent) {
                $asNew = 'new';
            }
        }
        $hasComments = !empty($commentsData[$article->articleId]);
?>

<div class="post <?= !$hasComments ? 'no-comments' : '' ?>" data-id="{$article->articleId}">
    <? if ($author->authorId == $__Author->authorId) { ?>
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
        <div class="hight-light {$asNew}">
            <div class="title">
                <a target="_blank" href="http://vk.com/id{$author->vkId}">{$author->FullName()}</a>
            </div>
            <div class="text">
                <?
                $contentPart1 = mb_substr($articleRecord->content, 0, 300);
                $contentPart2 = mb_substr($articleRecord->content, 300);
                $contentPart1 = !empty($contentPart1) ? $contentPart1 : ''
                ?>
                <div class="shortcut">
                    <?= nl2br(HtmlHelper::RenderToForm($contentPart1)) ?>
                    <? if($contentPart2) { ?>
                        ...<a href="javascript:;" class="show-cut">Показать полностью...</a>
                    <? } ?>
                </div>
                <? if($contentPart2) { ?>
                <div class="cut"><?= nl2br(HtmlHelper::RenderToForm($contentPart2)) ?></div>
                <? } ?>
            </div>

            <? if (!empty($articleRecord->photos)) { ?>
                <div class="attachments">
                <? foreach($articleRecord->photos as $photoItem) {
                    $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
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
                    <span class="date">{$article->createdAt->defaultFormat()}</span>
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