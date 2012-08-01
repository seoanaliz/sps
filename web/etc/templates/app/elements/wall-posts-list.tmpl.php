<?
    $articlesCount = empty($articlesCount) ? 0 : $articlesCount;
    /** @var $articles Article[] */
    /** @var $articleRecords ArticleRecord[] */
    /** @var $authors Author[] */
    /** @var $targetFeeds TargetFeed[] */

    if (!empty($articles)) {
        foreach($articles as $article) {
            $articleRecord  = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
            $author         = !empty($authors[$article->authorId]) ? $authors[$article->authorId] : null;
            $targetFeed     = !empty($targetFeeds[$article->targetFeedId]) ? $targetFeeds[$article->targetFeedId] : null;
            ?>{increal:tmpl://app/elements/wall-post.tmpl.php}<?
        }
    }

    $articlesCountText = (empty($articlesCount) ? 'нет' : $articlesCount) . ' ' . LocaleLoader::Translate('fe.common.records.declension' . TextHelper::GetDeclension( $articlesCount ));
?>
<? if ($hasMore) {?>
    <div id="wall-show-more" class="show-more">Еще</div>
<? } ?>
<script type="text/javascript">
    $('#wall > .title .text').text('{$articlesCountText}');
</script>