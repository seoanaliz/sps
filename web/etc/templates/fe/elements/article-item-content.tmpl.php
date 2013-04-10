<?
/**
 * @var $articleRecord ArticleRecord
 */
$contentPart1 = mb_substr($articleRecord->content, 0, 300);
$contentPart2 = mb_substr($articleRecord->content, 300);
$contentPart1 = !empty($contentPart1) ? $contentPart1 : '';
?>
<div class="shortcut"><?= nl2br(HtmlHelper::RenderToForm($contentPart1)) ?></div>
<? if ($contentPart2) { ?>
    <a class="show-cut">Показать полностью...</a>
    <div class="cut"><?= nl2br(HtmlHelper::RenderToForm($contentPart2)) ?></div>
<? } ?>
<? if (!empty($articleRecord->link)) { ?>
    <div class="link-info-content">
        <div class="link-description-content">
            <img src="{web:images://fe/ajax-loader.gif}" alt="" class="<?= !empty($extLinkLoader) ? 'ajax-loader-ext' : 'ajax-loader' ?>" rel="{form:$articleRecord->link}" />
        </div>
    </div>
<? } ?>
<? if (!empty($articleRecord->photos)) {
    $i = 0;
    ?>
    <div class="images-ready">
        <? foreach($articleRecord->photos as $photoItem) {
            $i++;
            $path = MediaUtility::GetArticlePhoto($photoItem);
            $photoTitle = !empty($photoItem['title']) ? $photoItem['title'] : '';
            $photoTitle = nl2br($photoTitle);
            ?>
            <a class="fancybox-thumb" rel="fancybox-thumb-{$articleRecord->articleId}" href="{$path}" title="{form:$photoTitle}">
                <div class="post-image <?= !empty($sourceFeed) && SourceFeedUtility::IsTopFeed($sourceFeed) ? 'post-image-top' : '' ?>">
                    <img src="{$path}" alt="" />
                </div>
            </a>
        <? } ?>
    </div>
<? } ?>
