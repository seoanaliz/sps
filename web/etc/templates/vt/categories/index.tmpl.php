<?php
    /** @var Category[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.menu.categories");

    $grid = array(
        "columns" => array(
            LocaleLoader::Translate( "vt.mobile.index" )
           ,LocaleLoader::Translate( "vt.mobile.publicId" )
           ,LocaleLoader::Translate( "vt.mobile.name" )
           ,LocaleLoader::Translate( "vt.mobile.mask" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "publicId")
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> CategoryFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://categories/" )
        , "addpath"		=> Site::GetWebPath( "vt://categories/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.category.deleteString")
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
                    <label>{lang:vt.mobile.index}</label>
                    <?= FormHelper::FormInput( "search[index]", $search['index'], 'index', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.publicId}</label>
                    <?= FormHelper::FormInput( "search[publicId]", $search['publicId'], 'publicId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.name}</label>
                    <?= FormHelper::FormInput( "search[name]", $search['name'], 'name', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.mask}</label>
                    <?= FormHelper::FormInput( "search[mask]", $search['mask'], 'mask', null, array( 'size' => 80 ) ); ?>
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
                <td>{$object.name}</td>
                <td>{$object.mask}</td>

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