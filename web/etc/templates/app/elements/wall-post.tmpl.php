<?
    /** @var $article Article */
    /** @var $articleRecord ArticleRecord */
    /** @var $author Author */
    /** @var $targetFeed TargetFeed */
    /** @var $targetInfo array */

    if (!empty($article)) {
?>

<div class="post" data-id="{$article->articleId}">
    <? if ($author->authorId == $__Author->authorId) { ?>
        <div class="delete"></div>
    <? } ?>
    <? if (!empty($targetFeed) && !empty($targetInfo[$targetFeed->targetFeedId])) { ?>
    <div class="photo">
        <a target="_blank" href="http://vk.com/public{$targetFeed->externalId}">
            <img src="<?= $targetInfo[$targetFeed->targetFeedId]['img'] ?>" alt="" />
        </a>
    </div>
    <? } ?>
    <div class="content">
        <div class="title">
            <a target="_blank" href="http://vk.com/public{$targetFeed->externalId}">{form:$targetFeed->title}</a>
        </div>
        <div class="text"><?= nl2br(HtmlHelper::RenderToForm($articleRecord->content)) ?></div>

        <?
        if (!empty($articleRecord->photos)) {
            ?><div class="attachments"><?
                foreach($articleRecord->photos as $photoItem) {
                    $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                    ?><img src="{$path}" alt="" /><?
                }
            ?></div><?
        }
        ?>

        <div class="sign clear-fix">
            <div class="user-info">
                <? if (!empty($author)) { ?>
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
            </div>
        </div>
        {increal:tmpl://app/elements/wall-comments.tmpl.php}
    </div>
</div>
<? } ?>