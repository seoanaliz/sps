<?
    /** @var $article Article */
    /** @var $articleRecord ArticleRecord */
    /** @var $sourceFeed SourceFeed */
    /** @var $sourceInfo array */

    if (!empty($article)) {

        $extLinkLoader  = false;

        if (!empty($sourceFeed) && SourceFeedUtility::IsTopFeed($sourceFeed) && !empty($articleRecord->photos)) {
            $extLinkLoader = true;
        }
?>

<div class="post bb <?= (empty($sourceFeed) || $sourceFeed->type != SourceFeedUtility::Ads) ? 'movable' : '' ?>" data-group="{$article->sourceFeedId}" data-id="{$article->articleId}">
    <? if (!empty($sourceInfo[$article->sourceFeedId])) { ?>
        <div class="l d-hide">
            <div class="userpic"><img src="<?=$sourceInfo[$article->sourceFeedId]['img']?>" alt="" /></div>
        </div>
        <div class="name d-hide"><?=$sourceInfo[$article->sourceFeedId]['name']?></div>
    <? } else if (!empty($author)) { ?>
        <div class="l d-hide">
            <div class="userpic"><img src="{$author->avatar}" alt="" /></div>
        </div>
        <div class="name d-hide">{$author->FullName()}</div>
    <? } ?>
    <div class="content">
        <?
        $contentPart1 = mb_substr($articleRecord->content, 0, 300);
        $contentPart2 = mb_substr($articleRecord->content, 300);
        $contentPart1 = !empty($contentPart1) ? $contentPart1 : ''
        ?>
        <div class="shortcut"><?= nl2br(HtmlHelper::RenderToForm($contentPart1)) ?></div>
        <? if($contentPart2) { ?>
        <a class="show-cut">Показать полностью...</a>
        <div class="cut"><?= nl2br(HtmlHelper::RenderToForm($contentPart2)) ?></div>
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

                    ?><a class="fancybox-thumb" rel="fancybox-thumb-{$article->articleId}" href="{$path}" title="{form:$photoTitle}">
                        <div class="post-image <?= SourceFeedUtility::IsTopFeed($sourceFeed) ? 'post-image-top' : '' ?>">
                            <img src="{$path}" alt="" />
                        </div>
                    </a>
                    <?
                }
                ?></div><?
        }
        ?>
    </div>
    <div class="bottom d-hide">
        <div class="l">
            <span class="timestamp">{$article->createdAt->defaultFormat()}</span> |
            <a class="edit">Редактировать</a> |
            <a class="clear-text">Очистить текст</a>
        </div>
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
                    <a href="{$articleLinkPrefix}{$article->externalId}" target="_blank">Оригинал</a>
                <? } else { ?>
                    Добавлена вручную
                <? } ?>
            </span>
            <span class="likes spr"></span><span class="likes-count"><?= ($article->rate > 100) ? 'TOP' : $article->rate ?></span>
        </div>
    </div>
    <div class="delete spr"></div>
    <div class="clear"></div>

    <? if (!empty($article->authorId)) { ?>
    <div class="comments">
        <div class="list">
            {increal:tmpl://app/elements/wall-comments-list.tmpl.php}
        </div>
        <div class="new-comment">
            <div class="photo">
                <img src="{$__Editor->avatar}" alt="" />
            </div>
            <div class="textarea-wrap">
                <textarea rows="" cols="" placeholder="Ваш текст..."></textarea>
            </div>
            <div class="actions">
                <button class="button send">Отправить</button>
                <span class="text">Ctrl+Enter</span>
            </div>
        </div>
        <div class="moderation">
            <div class="actions">
                <button class="button approve">Одобрить</button>
                <button class="button white reject">Отклонить</button>
            </div>
        </div>
    </div>
    <? } ?>
    <div class="clear"></div>
</div>
<? } ?>
