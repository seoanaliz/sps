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
                <div class="text-wrap">
                    <?= nl2br($articleRecord->content) ?>
                </div>

                <? if (!empty($articleRecord->photos)) { ?>
                <div class="images">
                    <? foreach($articleRecord->photos as $photoItem) { ?>
                        <img src="<?= MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation) ?>">
                    <? } ?>
                </div>
                <? } ?>
            </div>
            <div class="bottom d-hide">
                <div class="l"><span class="timestamp">{$article->createdAt->defaultFormat()}</span> | <a href="javascript:;">Редактировать</a></div>
                <div class="r">
                    <span class="original">
                        <? if($article->externalId != -1){ ?>
                            <a href="http://vk.com/wall-{$article->externalId}" target="_blank">Оригинал</a>
                        <? } else { ?>
                            Добавлена вручную
                        <? } ?>
                    </span> | <span class="likes spr"></span><span class="likes-count">{$articleRecord->likes}</span>
                </div>
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