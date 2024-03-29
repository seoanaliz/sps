<?php
    /** @var SourceFeed[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.screens.sourceFeed.list");

    $grid = array(
        "columns" => array(
           LocaleLoader::Translate( "vt.sourceFeed.title" )
            , LocaleLoader::Translate( "vt.common.externalId" )
            , LocaleLoader::Translate( "vt.sourceFeed.useFullExport" )
            , LocaleLoader::Translate( "vt.sourceFeed.targetFeedIds" )
            , LocaleLoader::Translate( "vt.sourceFeed.type" )
            , LocaleLoader::Translate( "vt.sourceFeed.statusId" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "title", 1 => "externalId", 2 => "useFullExport", 4 => "type", 5 => "statusId")
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> SourceFeedFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://source-feeds/" )
        , "addpath"		=> Site::GetWebPath( "vt://source-feeds/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.sourceFeed.deleteString")
    );
	
	$__breadcrumbs = array( array( 'link' => $grid['basepath'], 'title' => $__pageTitle ) );
?>
{increal:tmpl://vt/header.tmpl.php}
<div class="main">
	<div class="inner">
		{increal:tmpl://vt/elements/menu/breadcrumbs.tmpl.php}
		<div class="pagetitle">
			<? if( $grid['allowAdd'] ) { ?>
			<div class="controls"><a href="{$grid[addpath]}" class="add"><span>{lang:vt.common.add}</span></a></div>
			<? } ?>
			<h1>{$__pageTitle}</h1>
		</div>
		{$grid[description]}
		<div class="search<?= $hideSearch == "true" ? " closed" : ""  ?>">
			<a href="#" class="search-close"><span>{lang:vt.common.closeSearch}</span></a>
			<a href="#" class="search-open"><span>{lang:vt.common.openSearch}</span></a>
			<form class="search-form" id="searchForm" method="post" action="{$grid[basepath]}">
				<input type="hidden" value="1" name="searchForm" />
				<input type="hidden" value="" id="pageId" name="page" />
				<input type="hidden" value="{$grid[pageSize]}" id="pageSize" name="search[pageSize]" />
				<input type="hidden" value="{form:$sortField}" id="sortField" name="sortField" />
				<input type="hidden" value="{form:$sortType}" id="sortType" name="sortType" />
                <div class="row">
                    <label>{lang:vt.sourceFeed.title}</label>
                    <?= FormHelper::FormInput( "search[title]", $search['title'], 'title', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.common.externalId}</label>
                    <?= FormHelper::FormInput( "search[externalId]", $search['externalId'], 'externalId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.sourceFeed.type}</label>
                    <?= FormHelper::FormSelect( "search[type]", SourceFeedUtility::$Types, "", "", $search['type'], null, null, true ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.sourceFeed.statusId}</label>
                    <?= FormHelper::FormSelect( "search[statusId]", StatusUtility::$Common[$__currentLang], "", "", $search['statusId'], null, null, true ); ?>
                </div>
				<input type="submit" value="{lang:vt.common.find}" />
			</form>
		</div>
		
		<!-- GRID -->
		{increal:tmpl://vt/elements/datagrid/header.tmpl.php}
<?php
    $langEdit   = LocaleLoader::Translate( "vt.common.edit" );
    $langDelete = LocaleLoader::Translate( "vt.common.delete" );

    foreach ( $list as $SourceFeed )  {
        $id         = $SourceFeed->sourceFeedId;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td class="header">{$SourceFeed.title}</td>
                <td>
                    <? if ($SourceFeed->type == SourceFeedUtility::Source) : ?>
                        <a href="http://vk.com/wall-{form:$SourceFeed.externalId}" target="_blank">http://vk.com/wall-{form:$SourceFeed.externalId}</a>
                    <? elseif ($SourceFeed->type == SourceFeedUtility::Albums): ?>
                        <a href="http://vk.com/album-{form:$SourceFeed.externalId}" target="_blank">http://vk.com/album-{form:$SourceFeed.externalId}</a>
                    <? endif; ?>
                </td>
                <td><?= StatusUtility::GetBoolTemplate($SourceFeed->useFullExport) ?></td>
                <td class="left">
                    <?
                        $targetFeedIds = explode(',', $SourceFeed->targetFeedIds);
                        $objectFeeds = array();
                        foreach ($targetFeedIds as $targetFeedId) {
                            if (!empty($targetFeeds[$targetFeedId])) {
                                $objectFeeds[] = $targetFeeds[$targetFeedId];
                            }
                        }

                        if (!empty($objectFeeds)) {
                            $links = array_map(function($val){
                                return "<a href='http://vk.com/public$val->externalId' target='_blank'>$val->title</a>";
                            }, $objectFeeds);

                            $links = implode(', ', $links);
                            echo $links;
                        } else {
                            ?><span class="status red" title="Нет">Нет</span><?
                        }
                    ?>
                </td>
                <td><?= SourceFeedUtility::$Types[$SourceFeed->type] ?></td>
                <td><?= StatusUtility::GetStatusTemplate($SourceFeed->statusId) ?></td>
				<td width="10%">
					<ul class="actions">
						<li class="edit"><a href="{$editpath}" title="{$langEdit}">{$langEdit}</a></li><li class="delete"><a href="#" class="delete-object" title="{$langDelete}">{$langDelete}</a></li>
					</ul>
				</td>
	        </tr>
<?php
    }
?>
		{increal:tmpl://vt/elements/datagrid/footer.tmpl.php}
		<!-- EOF GRID -->
	</div>
</div>
{increal:tmpl://vt/footer.tmpl.php}