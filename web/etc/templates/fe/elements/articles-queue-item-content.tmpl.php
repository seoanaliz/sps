<?
/** @var $articleRecord ArticleRecord */

$content = nl2br(HtmlHelper::RenderToForm($articleRecord->content));
$collapsed = (strlen($content) > 50) ? 'collapsed' : false;
?>
    <div class="text {$collapsed}">
        {$content}
        <? if ($collapsed) { ?>
            <span class="toggle-text"></span>
        <? } ?>
    </div>
<? if (!empty($articleRecord->link)) { ?>
    <div class="link-info-content">
        <div class="link-description-content">
            <img src="{web:images://fe/ajax-loader.gif}" alt="" class="ajax-loader" rel="{form:$articleRecord->link}"/>
        </div>
    </div>
<? } ?>
<? if (!empty($articleRecord->photos)) { ?>
    <div class="images">
        <? foreach($articleRecord->photos as $photoItem) { ?>
            <a class="img fancybox-thumb image-wrap" rel="fancybox-thumb-<?=$articleRecord->articleRecordId?>" href="<?= MediaUtility::GetArticlePhoto($photoItem); ?>">
                <img src="<?= MediaUtility::GetArticlePhoto($photoItem); ?>">
            </a>
        <? } ?>
    </div>
<? } ?>