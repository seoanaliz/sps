<?
    /** @var $articles Article[] */
    /** @var $articleRecords articleRecord[] */
    /** @var $sourceFeed SourceFeed */
    if (!empty($articles)) {
        foreach($articles as $article) {
            $articleRecord = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
            ?>
        <div class="post bb" data-id="{$article->articleId}">
            <div class="l d-hide">
                <div class="userpic"><img src="{$sourceInfo[img]}" /></div>
            </div>
            <div class="name d-hide">{$sourceInfo[name]}</div>
            <div class="content">
                <?= nl2br($articleRecord->content) ?>
                <? foreach($articleRecord->photos as $photoItem) { ?>
                <br /><img src="<?= MediaUtility::GetFilePath( 'Article', 'photos', 'small', $photoItem['filename'], MediaServerManager::$MainLocation) ?>">
                <? } ?>
            </div>
            <div class="bottom d-hide">
                <div class="l"><span class="timestamp">{$article->createdAt->defaultFormat()}</span> | <a href="javascript:;">Редактировать</a></div>
                <div class="r"><span class="original"><a href="http://vk.com/wall-{$article->externalId}" target="_blank">Оригинал</a></span> | <span class="likes spr"></span><span class="likes-count">{$articleRecord->likes}</span></div>
            </div>
            <div class="delete spr"></div>
            <div class="clear"></div>
        </div>
        <?
        }
    }
?>
<script type="text/javascript">
    <?
        if (!empty($hasMore)) {
            ?>$("#wallloadmore").removeClass('hidden');<?
        } else {
            ?>$("#wallloadmore").addClass('hidden');<?
        }
    ?>
</script>