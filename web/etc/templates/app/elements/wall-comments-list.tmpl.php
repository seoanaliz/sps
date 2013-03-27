<?
/**
 * @var int $authorId - ид автора текущего пользователя
 * @var $article Article
 * @var array $commentsData
 * @var bool $isWebUserEditor
 */

if (!empty($commentsData[$article->articleId])) {
    if ($commentsData[$article->articleId]['count'] > CommentUtility::LAST_COUNT) {
        if (!empty($showHideBtn)) {
            ?>
        <div class="show-more hide">Скрыть комментарии</div><?
        } else {
            ?>
        <div class="show-more">
            Показать еще <?= ($commentsData[$article->articleId]['count'] - CommentUtility::LAST_COUNT) ?>
            <? if (!empty($commentsData[$article->articleId]['countNewCollapsed'])) { ?>
<!--            <span class="counter">+--><?//=$commentsData[$article->articleId]['countNewCollapsed']?><!--</span>-->
            <? } ?>
        </div>
        <?
        }
    }
    foreach ($commentsData[$article->articleId]['comments'] as $comment) {
        ?>{increal:tmpl://app/elements/wall-comment.tmpl.php}<?
    }
}
?>
