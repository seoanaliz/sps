<?php
    /** @var ArticleQueue $object */

    $prefix = "articleQueue";

    if ( empty( $errors ) ) $errors = array();
	if ( empty( $jsonErrors ) ) $jsonErrors = '{}';

    if ( !empty($errors["fatal"] ) ) {
		?><h3 class="error"><?= LocaleLoader::Translate( 'errors.fatal.' . $errors["fatal"] ); ?></h3><?
	}

    CssHelper::PushFile( 'js://ext/uploadify/uploadify.css' );
    JsHelper::PushFiles( array(
        'js://ext/swfobject/swfobject.js'
        , 'js://ext/uploadify/jquery.uploadify.js'
        , 'js://vt/files.js'
    ));
?>
<div class="tabs">
	<?= FormHelper::FormHidden( 'selectedTab', !empty( $selectedTab ) ? $selectedTab : 0, 'selectedTab' ); ?>
    <ul class="tabs-list">
        <li><a href="#page-0">{lang:vt.common.commonInfo}</a></li>
        <li><a href="#page-1">{lang:vt.article.recordInfo}</a></li>
    </ul>

    <div id="page-0" class="tab-page rows">
        <div data-row="articleId" class="row required">
            <label>{lang:vt.articleQueue.articleId}</label>
            <?= FormHelper::FormInput( $prefix . '[articleId]', $object->articleId, 'articleId', null, array( 'size' => 80 ) ); ?>
        </div>
        <div data-row="type" class="row required">
            <label>{lang:vt.sourceFeed.type}</label>
            <?= FormHelper::FormSelect( $prefix . '[type]', GridLineUtility::$Types, "", "", $object->type, null, null, false ); ?>
        </div>
        <div data-row="startDate" class="row required">
            <label>{lang:vt.articleQueue.startDate}</label>
            <?= FormHelper::FormDateTime( $prefix . '[startDate]', $object->startDate, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="endDate" class="row required">
            <label>{lang:vt.articleQueue.endDate}</label>
            <?= FormHelper::FormDateTime( $prefix . '[endDate]', $object->endDate, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="sentAt" class="row">
            <label>{lang:vt.articleQueue.sentAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[sentAt]', $object->sentAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="targetFeedId" class="row required">
            <label>{lang:vt.articleQueue.targetFeedId}</label>
            <?= FormHelper::FormSelect( $prefix . '[targetFeedId]', $targetFeeds, "targetFeedId", "title", $object->targetFeedId, null, null, false ); ?>
        </div>
        <div data-row="author" class="row">
            <label>{lang:vt.articleQueue.author}</label>
            <?= FormHelper::FormInput( $prefix . '[author]', $object->author, 'author', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
        </div>
        <div data-row="externalId" class="row">
            <label>{lang:vt.common.externalId}</label>
            <?= FormHelper::FormInput( $prefix . '[externalId]', $object->externalId, 'externalId', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
        </div>
        <div data-row="externalLikes" class="row">
            <label>{lang:vt.articleQueue.externalLikes}</label>
            <?= FormHelper::FormInput( $prefix . '[externalLikes]', $object->externalLikes, 'externalLikes', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
        </div>
        <div data-row="externalRetweets" class="row">
            <label>{lang:vt.articleQueue.externalRetweets}</label>
            <?= FormHelper::FormInput( $prefix . '[externalRetweets]', $object->externalRetweets, 'externalRetweets', null, array( 'size' => 80, 'style' => 'width: 200px;' ) ); ?>
        </div>
        <div data-row="statusId" class="row required">
            <label>{lang:vt.articleQueue.statusId}</label>
            <?= FormHelper::FormSelect( $prefix . '[statusId]', StatusUtility::$Queue[$__currentLang], "", "", $object->statusId, null, null, false ); ?>
        </div>
        <div data-row="deleteAt" class="row">
            <label>{lang:vt.articleQueue.deleteAt}</label>
            <?= FormHelper::FormDateTime( $prefix . '[deleteAt]', $object->deleteAt, 'd.m.Y G:i' ); ?>
        </div>
        <div data-row="isDeleted" class="row">
            <label>{lang:vt.articleQueue.isDeleted}</label>
            <?= FormHelper::FormCheckBox( $prefix . '[isDeleted]', "TRUE", "", "", $object->isDeleted); ?>
        </div>
        <div data-row="collectLikes" class="row">
            <label>{lang:vt.articleQueue.collectLikes}</label>
            <?= FormHelper::FormCheckBox( $prefix . '[collectLikes]', "TRUE", "", "", $object->collectLikes); ?>
        </div>
	</div>
    <div id="page-1" class="tab-page rows">
        {increal:tmpl://vt/articles/record.tmpl.php}
    </div>
</div>
<script type="text/javascript">
	var jsonErrors = {$jsonErrors};
</script>
 