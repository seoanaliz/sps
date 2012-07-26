<?
    $articlesCount = empty($articlesCount) ? 0 : $articlesCount;
    /** @var $articles Article[] */
    /** @var $articleRecords ArticleRecord[] */
    if (!empty($articles)) {
        foreach($articles as $article) {
            $articleRecord  = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
            ?>{increal:tmpl://app/elements/wall-post.tmpl.php}<?
        }
    }
?>
<? if ($hasMore) {?>
    <div id="wall-show-more" class="show-more">Еще</div>
<? } ?>