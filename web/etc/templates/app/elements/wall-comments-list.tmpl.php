<!--Ajax template-->
<? if (!empty($comments_count) && $comments_count > 3) { ?>
<div class="show-more">Show all 6 comments</div>
<? } else { ?>
<!--div class="show-more hide">Hide comments</div-->
<? } ?>
{increal:tmpl://app/elements/wall-comment.tmpl.php}
{increal:tmpl://app/elements/wall-comment.tmpl.php}
{increal:tmpl://app/elements/wall-comment.tmpl.php}