<?
/** @var $article Article */
/** @var $articleRecord ArticleRecord */
/** @var $sourceFeed SourceFeed|null */
/** @var $sourceInfo array */
/** @var $isWebUserEditor bool */
/** @var $sourceFeedType String */
/** $var $repostArticleRecord ArticleRecord */

if (!empty($article)) {
    $canEditPost = true;
    $extLinkLoader = false;
    $isPostMovable = false;
    $isPostRelocatable = true;
    $showApproveBlock = $isWebUserEditor && $article->articleStatus == Article::STATUS_REVIEW;
    $repostOrigin = false;

    if (!empty($sourceFeed) && SourceFeedUtility::IsTopFeed($sourceFeed) && !empty($articleRecord->photos)) {
        $extLinkLoader = true;
    }

    if ($sourceFeedType == SourceFeedUtility::Ads) {
        $isPostRelocatable = false;
    }

    if ($isWebUserEditor) {
        switch ($sourceFeedType) {
            case SourceFeedUtility::Ads:
            case SourceFeedUtility::Source:
            case SourceFeedUtility::Topface:
                $isPostMovable = true;
                break;
            case SourceFeedUtility::Authors:
            case SourceFeedUtility::Albums:
                if ($article->articleStatus == Article::STATUS_APPROVED && is_null($article->queuedAt)) {
                    $isPostMovable = true;
                }
                break;
        }
    }

    $canEditPost = true;
    if (!$isWebUserEditor) {
        $canEditPost = $article->articleStatus != Article::STATUS_APPROVED;
    }
    if (!empty($sourceFeed) && $sourceFeed->type == SourceFeedUtility::Albums) {
        $canEditPost = false;
    }

    if( isset( $repostArticleRecord) && $repostArticleRecord ) {
        $repostOrigin = trim( $articleRecord->repostExternalId, '-' );
        $articleRecord = $repostArticleRecord;
    }
?>
<div
    class="post bb
    <?= $isPostMovable ? 'movable' : '' ?>
    <?= $canEditPost ? 'editable' : '' ?>
    <?= !empty($author) ? 'author' : '' ?>
    <?= $isPostRelocatable ? 'relocatable' : '' ?>"
    data-group="{$article->sourceFeedId}"
    data-repost-id="{$articleRecord->repostExternalId}"
    <? if (!empty($author)) { ?>
        data-author-id="{$author->authorId}"
    <? } ?>
    data-id="{$article->articleId}">
    <? if (!empty($sourceInfo[$article->sourceFeedId])) { ?>
        <div class="l d-hide">
            <div class="userpic">
                <img src="<?=$sourceInfo[$article->sourceFeedId]['img']?>" alt="" />
            </div>
        </div>
        <div class="name d-hide">
            <?=$sourceInfo[$article->sourceFeedId]['name']?>
        </div>
    <? } else if (!empty($author)) { ?>
        <div class="l d-hide">
            <div class="userpic"><img src="{$author->avatar}" alt="" /></div>
        </div>
        <div class="name d-hide">{$author->FullName()}</div>
    <? } ?>
    <div class="content">
        {increal:tmpl://fe/elements/article-item-content.tmpl.php}
    </div>
    <div class="bottom d-hide">
        <div class="l">
            <span class="timestamp">{$article->createdAt->defaultFormat()}</span>
            <? if ($canEditPost) { ?>|
                <a class="edit">Редактировать</a> |
                <a class="clear-text">Очистить текст</a>
            <? } ?>
        </div>
        <div class="r">
            <? if (!empty($repostArticleRecord)) { ?>
                <?//@todo ссылка на пост ?>
                <span class="hash-span" title="Пост с репостом"><b>Репост</b></span>
            <? } ?>
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
                <? if ($article->externalId != -1) {
                        $original_id = trim( $article->externalId, '-');
                ?>
                    <a href="{$articleLinkPrefix}{$original_id}" target="_blank">Оригинал</a>
                <?} elseif ( $repostOrigin ) { ?>
                <a href="{$articleLinkPrefix}{$repostOrigin}" target="_blank">Оригинал</a>
                <? } else {
                    $sign = '';
                    if (!is_null($article->sentAt)) {
                        $sign = 'Опубликовано';
                    } else {
                        switch ($article->articleStatus) {
                            case Article::STATUS_APPROVED:
                                $sign = 'Ожидает публикации';
                                break;
                            case Article::STATUS_REJECT:
                                $sign = 'Отклонено';
                                break;
                            case Article::STATUS_REVIEW:
                                $sign = 'Ожидает рассмотрения';
                                break;
                        }
                    }
                    ?>
                    {$sign}
                <? } ?>
            </span>
            <? if ($article->rate > 0) { ?>
                <span class="likes spr"></span>
                <span class="likes-count">
                    <?= ($article->rate > 100) ? 'TOP' : $article->rate ?>
                </span>
            <? } ?>
        </div>
    </div>
    <? if ($canEditPost) { ?>
        <div class="delete spr"></div>
    <? } ?>
    <div class="clear"></div>

    <? if (!empty($article->authorId)) { ?>
        <div class="comments">
            <div class="list">
                {increal:tmpl://app/elements/wall-comments-list.tmpl.php}
            </div>
            <div class="new-comment" style="<? if ($showApproveBlock) { ?>display: none<? } ?>">
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
        </div>
    <? } ?>
    <? if ($showApproveBlock) { ?>
        <div class="moderation">
            <div class="actions">
                <button class="button approve">Одобрить</button>
                <button class="button white reject">Отклонить</button>
            </div>
        </div>
    <? } ?>
    <div class="clear"></div>
</div>
<? } ?>
