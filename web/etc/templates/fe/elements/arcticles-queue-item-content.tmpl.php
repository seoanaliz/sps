<?
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
        <img src="{web:images://fe/ajax-loader.gif}" alt="" class="ajax-loader" rel="{form:$articleRecord->link}" />
    </div>
</div>
<? } ?>
<? if (!empty($articleRecord->photos)) { ?>
<div class="images">
    <? $i = 0; ?>
    <? foreach($articleRecord->photos as $photoItem) {
    $i++;
    $size = 'original';
    ?>
    <div class="img">
        <img src="<?= MediaUtility::GetArticlePhoto($photoItem); ?>">
    </div>
    <? } ?>
</div>
<? } ?>