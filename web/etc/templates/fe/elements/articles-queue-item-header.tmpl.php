<? if (UrlParser::IsContentWithWikiLink($articleRecord->content)) { ?>
<span class="attach-icon attach-icon-link" title="Пост с вики-ссылкой"><!-- --></span>
<? } ?>
<? if (UrlParser::IsContentWithLink($articleRecord->content) || !empty($articleRecord->link)) { ?>
<span class="attach-icon attach-icon-link-red" title="Пост с внешней ссылкой"><!-- --></span>
<? } ?>
<? if (UrlParser::IsContentWithHash($articleRecord->content) ) { ?>
<span class="hash-span" title="Пост с хештэгом">#hash</span>
<? } ?>
<? if (isset($isRepost) && $isRepost) { ?>
<span class="hash-span" title="Пост с репостом"><b>Репост</b></span>
<? } ?>