<?php
    /** @var ArticleRecord $articleRecord */

    $prefixRecord = "articleRecord";

    $__useEditor = true;
?>
<div data-row="content" class="row required">
    <label>{lang:vt.articleRecord.content}</label>
    <?= FormHelper::FormEditor( $prefixRecord . '[content]', $articleRecord->content, 'content', null, array( 'rows' => 5, 'cols' => 80 ) ); ?>
</div>
<div data-row="likes" class="row">
    <label>{lang:vt.articleRecord.likes}</label>
    <?= FormHelper::FormInput( $prefixRecord . '[likes]', $articleRecord->likes, 'likes', null, array( 'size' => 80, 'style' => 'width: 100px;' ) ); ?>
</div>