<?
    $articlesCount = empty($articlesCount) ? 0 : $articlesCount;
    /** @var $articles Article[] */
    /** @var $articleRecords ArticleRecord[] */
    /** @var $sourceFeeds SourceFeed[] */
    /** @var $authors Author[] */
    /** @var $reviewArticleCount int */
    /** @var $showArticlesOnly bool */
?>
<? if ($reviewArticleCount && !$showArticlesOnly): ?>
    <div class="show-all-postponed">Показать <?=$reviewArticleCount?> отложенные записи</div>
<? endif; ?>
<?

    if (!empty($articles)) {
        foreach($articles as $article) {
            $articleRecord  = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
            $sourceFeed     = !empty($sourceFeeds[$article->sourceFeedId]) ? $sourceFeeds[$article->sourceFeedId] : new SourceFeed();
            $author         = !empty($authors[$article->authorId]) ? $authors[$article->authorId] : null;

            ?>{increal:tmpl://fe/elements/arcticle-item.tmpl.php}<?
        }
    }

    $articlesCountText = (empty($articlesCount) ? 'нет' : $articlesCount) . ' ' . LocaleLoader::Translate('fe.common.records.declension' . TextHelper::GetDeclension( $articlesCount ));
?>
<? if ($showArticlesOnly): ?>
<script type="text/javascript">
    $('.wall-title span.count').text('{$articlesCountText}');
    <?
        if (!empty($hasMore)) {
            ?>$("#wallloadmore").removeClass('hidden');<?
        } else {
            ?>$("#wallloadmore").addClass('hidden');<?
        }
    ?>
</script>
<? endif; ?>
