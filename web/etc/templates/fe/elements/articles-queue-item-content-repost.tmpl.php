<?
/** @var $articleRecord ArticleRecord */
/** @var $repostArticleRecord ArticleRecord */
/** @var $originalId */

$content = nl2br(HtmlHelper::RenderToForm($repostArticleRecord->content));
$collapsed = (strlen($content) > 50) ? 'collapsed' : false;
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
    <div class="text {$collapsed}">
        {$content}
        <? if ($collapsed) { ?>
            <span class="toggle-text"></span>
        <? } ?>
    </div>
<? if (!empty($repostArticleRecord->link)) { ?>
    <div class="link-info-content">
        <div class="link-description-content">
            <img src="{web:images://fe/ajax-loader.gif}" alt="" class="ajax-loader" rel="{form:$repostArticleRecord->link}" />
        </div>
    </div>
<? } ?>
<? if (!empty($repostArticleRecord->photos)) { ?>
    <div class="images">
        <? foreach($repostArticleRecord->photos as $photoItem) { ?>
            <div class="img">
                <img src="<?= MediaUtility::GetArticlePhoto($photoItem); ?>">
            </div>
        <? } ?>
    </div>
<? } ?>
</div>