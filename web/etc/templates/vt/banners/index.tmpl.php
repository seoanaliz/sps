<?php
    /** @var Banner[] $list */

    $__pageTitle = LocaleLoader::Translate( "vt.menu.banners");

    $grid = array(
        "columns" => array(
              LocaleLoader::Translate( "vt.mobile.bannerId" )
            , LocaleLoader::Translate( "vt.mobile.publicId" )
            , LocaleLoader::Translate( "vt.mobile.platform" )
            , LocaleLoader::Translate( "vt.mobile.prob" )
            , LocaleLoader::Translate( "vt.mobile.imgUrl" )
            , LocaleLoader::Translate( "vt.mobile.actionUrl" )
            , LocaleLoader::Translate( "vt.mobile.active" )
        )
        , "colspans"	=> array()
        , "sorts"		=> array(0 => "publicId", 1 => "prob")
        , "operations"	=> true
        , "allowAdd"	=> true
        , "canPages"	=> BannerFactory::CanPages()
        , "basepath"	=> Site::GetWebPath( "vt://banners/" )
        , "addpath"		=> Site::GetWebPath( "vt://banners/add" )
        , "title"		=> $__pageTitle
		, "description"	=> ''
        , "pageSize"	=> HtmlHelper::RenderToForm( $search["pageSize"] )
        , "deleteStr"	=> LocaleLoader::Translate( "vt.banner.deleteString")
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
                    <label>{lang:vt.mobile.bannerId}</label>
                    <?= FormHelper::FormInput( "search[bannerId]", $search['bannerId'], 'bannerId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.publicId}</label>
                    <?= FormHelper::FormInput( "search[publicId]", $search['publicId'], 'publicId', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.prob}</label>
                    <?= FormHelper::FormInput( "search[prob]", $search['prob'], 'prob', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.imgUrl}</label>
                    <?= FormHelper::FormInput( "search[imgUrl]", $search['imgUrl'], 'imgUrl', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.actionUrl}</label>
                    <?= FormHelper::FormInput( "search[actionUrl]", $search['actionUrl'], 'actionUrl', null, array( 'size' => 80 ) ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.platform}</label>
                    <?= FormHelper::FormSelect( "search[platform]", array('ios' => 'ios', 'android' => 'android'),null, null, $search['platform'], null, null, true ); ?>
                </div>
                <div class="row">
                    <label>{lang:vt.mobile.active}</label>
                    <?= FormHelper::FormSelect( "search[active]", array('on' => 'on', 'off' => 'off'),null, null, $search['active'], null, null, true ); ?>
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
                <td>{$object.bannerId}</td>
                <td>{$object.publicId}</td>
                <td>{$object.platform}</td>
                <td>{$object.prob}</td>
                <td>{$object.imgUrl}</td>
                <td>{$object.actionUrl}</td>
                <td>{$object.active}</td>
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