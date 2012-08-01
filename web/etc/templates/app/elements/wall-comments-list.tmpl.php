<?
    /** @var $article Article */
    /** @var $commentsData array */

    if (!empty($commentsData[$article->articleId])) {
        if ($commentsData[$article->articleId]['count'] > CommentUtility::LAST_COUNT) {
            ?><div class="show-more">Показать все <?= $commentsData[$article->articleId]['count'] ?></div><?
        }
        foreach ($commentsData[$article->articleId]['comments'] as $comment) {
            ?>{increal:tmpl://app/elements/wall-comment.tmpl.php}<?
        }
    }
?>