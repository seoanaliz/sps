<? if (!empty($articleRecord->link)) { ?>
<span class="attach-icon attach-icon-link" title="Пост со ссылкой"><!-- --></span>
<? } ?>
<? if (UrlParser::IsContentWithLink($articleRecord->content)) { ?>
<span class="attach-icon attach-icon-link-red" title="Пост со ссылкой в контенте"><!-- --></span>
<? } ?>
<? if (UrlParser::IsContentWithHash($articleRecord->content)) { ?>
<span class="hash-span" title="Пост с хештэгом">#hash</span>
<? } ?>
<? if ( isset( $is_repost) && $is_repost ) { ?>
<span class="hash-span" title="Пост с репостом"><b>Репост</b></span>
<? } ?>