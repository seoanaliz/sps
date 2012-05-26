<?
    /** @var $articles Article[] */
    /** @var $articleRecords ArticleRecord[] */
    /** @var $sourceFeeds SourceFeed[] */
    if (!empty($articles)) {
        foreach($articles as $article) {
            $articleRecord  = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
            $sourceFeed     = $sourceFeeds[$article->sourceFeedId];

            $extLinkLoader  = false;

            if ($sourceFeed->externalId == ParserVkontakte::TOP && !empty($articleRecord->photos)) {
                $extLinkLoader = true;
            }
            ?>
        <div class="post bb" data-id="{$article->articleId}">
            <div class="l d-hide">
                <div class="userpic"><img src="<?=$sourceInfo[$article->sourceFeedId]['img']?>" /></div>
            </div>
            <div class="name d-hide"><?=$sourceInfo[$article->sourceFeedId]['name']?></div>
            <div class="content">
                <?
                    $content = nl2br(HtmlHelper::RenderToForm($articleRecord->content));
                    $contentPart1 = mb_substr($content, 0, 300);
                    $contentPart2 = mb_substr($content, 300);
                    $contentPart1 = !empty($contentPart1) ? $contentPart1 : ''
                ?>
                <div class="shortcut">{$contentPart1}</div>
                <? if($contentPart2) { ?>
                    <a href="javascript:;" class="show-cut">Показать полностью...</a>
                    <div class="cut">{$contentPart2}</div>
                <? } ?>

                <?
                    if (!empty($articleRecord->link)) {
                        ?>
                        <div class="link-info-content">
                            <div class="link-description-content">
                                <img src="{web:images://fe/ajax-loader.gif}" alt="" class="<?= ($extLinkLoader) ? 'ajax-loader-ext' : 'ajax-loader' ?>" rel="{form:$articleRecord->link}" />
                            </div>
                        </div>
                        <?
                    }
                ?>

                <?
                    if (!empty($articleRecord->photos)) {
                        $i = 0;

                        ?><div class="images-ready"><?
                        foreach($articleRecord->photos as $photoItem) {
                            $i++;
                            $path = MediaUtility::GetFilePath( 'Article', 'photos', 'original', $photoItem['filename'], MediaServerManager::$MainLocation);
                            $photoTitle = !empty($photoItem['title']) ? $photoItem['title'] : '';
                            $photoTitle = nl2br($photoTitle);

                            if ($i == 1) {
                                $imgClass = 'first';
                            } else {
                                $imgClass = 'else';
                            }

                            ?><a class="fancybox-thumb" rel="fancybox-thumb-{$article->articleId}" href="{$path}" title="{form:$photoTitle}">
                                <div class="post-image <?= ($sourceFeed->externalId == ParserVkontakte::TOP) ? 'post-image-top' : '' ?>">
                                    <img src="{$path}" class="{$imgClass}" alt="" />
                                </div>
                            </a>
                            <?
                        }
                        ?></div><?
                    }
                ?>
            </div>
            <div class="bottom d-hide">
                <div class="l"><span class="timestamp">{$article->createdAt->defaultFormat()}</span> | <a class="edit" href="javascript:;">Редактировать</a> | <a class="clear-text" href="javascript:;">Очистить текст</a></div>
                <div class="r">
                    <? if (!empty($articleRecord->link)) { ?>
                        <span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>
                    <? } ?>
                    <? if (UrlParser::IsContentWithLink($articleRecord->content)) { ?>
                        <span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>
                    <? } ?>
                    <? if (UrlParser::IsContentWithHash($articleRecord->content)) { ?>
                        <span class="hash-span" title="Пост с хештэгом">#hash</span>
                    <? } ?>
                    <span class="original">
                        <? if($article->externalId != -1){ ?>
                            <a href="http://vk.com/wall-{$article->externalId}" target="_blank">Оригинал</a>
                        <? } else { ?>
                            Добавлена вручную
                        <? } ?>
                    </span> | <span class="likes spr"></span><span class="likes-count"><?= ($article->rate > 100) ? 'TOP' : $article->rate ?></span>
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