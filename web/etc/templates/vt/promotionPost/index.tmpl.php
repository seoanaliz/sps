<?php
    /** @var PromotionPost[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.menu.promPosts");

    $grid = array(
        "columns" => array(
             LocaleLoader::Translate( "vt.mobile.indexPP" )
            ,LocaleLoader::Translate( "vt.mobile.publicId" )
            ,LocaleLoader::Translate( "vt.mobile.platform" )
            ,LocaleLoader::Translate( "vt.mobile.headerText" )
            ,LocaleLoader::Translate( "vt.mobile.imgUrl" )
            ,LocaleLoader::Translate( "vt.mobile.shareText" )
            ,LocaleLoader::Translate( "vt.mobile.actionText" )
            ,LocaleLoader::Translate( "vt.mobile.actionUrl" )
            ,LocaleLoader::Translate( "vt.mobile.active" )
            ,LocaleLoader::Translate( "vt.mobile.showsCount" )
            ,LocaleLoader::Translate( "vt.mobile.imageHeight" )
            ,LocaleLoader::Translate( "vt.mobile.imageWidth" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array()
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> PromotionPostFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://promotionPost/" )
        , "addpath"		=> Site::GetWebPath( "vt://promotionPost/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.promotionPost.deleteString")
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
                    <label>{lang:vt.mobile.publicId}</label>
                    <?= FormHelper::FormInput( "search[publicId]", $search['publicId'], 'publicId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.platform}</label>
                    <?= FormHelper::FormInput( "search[platform]", $search['platform'], 'platform', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.headerText}</label>
                    <?= FormHelper::FormInput( "search[headerText]", $search['headerText'], 'headerText', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.shareText}</label>
                    <?= FormHelper::FormInput( "search[text]", $search['text'], 'text', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.actionText}</label>
                    <?= FormHelper::FormInput( "search[actionText]", $search['actionText'], 'actionText', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.active}</label>
                    <?= FormHelper::FormInput( "search[active]", $search['active'], 'active', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.showsCount}</label>
                    <?= FormHelper::FormInput( "search[showsCount]", $search['showsCount'], 'showsCount', null, array( 'size' => 80 ) ); ?>
                </div>
				<input type="submit" value="{lang:vt.common.find}" />
			</form>
		</div>
		
		<!-- GRID -->
		{increal:tmpl://vt/elements/datagrid/header.tmpl.php}
<?php
    $langEdit   = LocaleLoader::Translate( "vt.common.edit" );
    $langDelete = LocaleLoader::Translate( "vt.common.delete" );

    foreach ( $list as $object )  {
        $id         = $object->id;
        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td>{$object.index}</td>
                <td>{$object.publicId}</td>
                <td>{$object.platform}</td>
                <td>{$object.headerText}</td>
                <td>{$object.imgUrl}</td>
                <td>{$object.text}</td>
                <td>{$object.actionText}</td>
                <td>{$object.actionUrl}</td>
                <td>{$object.active}</td>
                <td>{$object.showsCount}</td>
                <td>{$object.image_height}</td>
                <td>{$object.image_width}</td>
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