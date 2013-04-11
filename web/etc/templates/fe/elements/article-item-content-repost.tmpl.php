<?php
/** @var $articleRecord ArticleRecord */
/** @var $repostArticleRecord ArticleRecord */
/** @var $originalId */

$contentPart1 = mb_substr($repostArticleRecord->content, 0, 300);
$contentPart2 = mb_substr($repostArticleRecord->content, 300);
$contentPart1 = !empty($contentPart1) ? $contentPart1 : '';
$sourceVkId = current(explode('_', $originalId));
$sourceVkURL = 'http://vk.com/' . ($sourceVkId > 0 ? 'id' : 'public') . trim($sourceVkId, '-');
?>
<div class="repost">
    <div class="repost-title">
        <div class="repost-image">
            <a href="{$sourceVkURL}" target="_blank"><img src="{$repostArticleRecord->repostPublicImage}" /></a>
        </div>
        <div class="repost-link">
            <a href="{$sourceVkURL}" target="_blank">{$repostArticleRecord->repostPublicTitle}</a>
        </div>
    </div>
    <div class="shortcut"><?= nl2br(HtmlHelper::RenderToForm($contentPart1)) ?></div>
    <? if ($contentPart2) { ?>
        <a class="show-cut">Показать полностью...</a>
        <div class="cut"><?= nl2br(HtmlHelper::RenderToForm($contentPart2)) ?></div>
    <? } ?>
    <? if (!empty($repostArticleRecord->link)) { ?>
        <div class="link-info-content">
            <div class="link-description-content">
                <img src="{web:images://fe/ajax-loader.gif}" alt="" class="<?= !empty($extLinkLoader) ? 'ajax-loader-ext' : 'ajax-loader' ?>" rel="{form:$repostArticleRecord->link}" />
            </div>
        </div>
    <? } ?>
    <? if (!empty($repostArticleRecord->photos)) {
        $i = 0;
        ?>
        <div class="images-ready">
            <? foreach($repostArticleRecord->photos as $photoItem) {
                $i++;
                $path = MediaUtility::GetArticlePhoto($photoItem);
                $photoTitle = !empty($photoItem['title']) ? $photoItem['title'] : '';
                $photoTitle = nl2br($photoTitle);
                ?>
                <a class="fancybox-thumb" rel="fancybox-thumb-{$repostArticleRecord->articleId}" href="{$path}" title="{form:$photoTitle}">
                    <div class="post-image <?= !empty($sourceFeed) && SourceFeedUtility::IsTopFeed($sourceFeed) ? 'post-image-top' : '' ?>">
                        <img src="{$path}" alt="" />
                    </div>
                </a>
            <? } ?>
        </div>
    <? } ?>
</div>

