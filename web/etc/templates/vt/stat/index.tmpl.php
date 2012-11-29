<?php
    /** @var Publisher[] $list */

    $__pageTitle = 'Стата';

    $grid = array(
        "columns" => array(
                'дата'
            ,   'уникальных посетителей'
            ,   'аудитория'
            ,   'обмены'
            ,   'обмены, подп.'
            ,   'обмены, посит.'

        )
        , "colspans"	=> array()
        , "sorts"		=> array()
        , "operations"	=> false
        , "allowAdd"	=> false
        , "canPages"	=> false
        , "basepath"	=> Site::GetWebPath( "vt://stat/" )
        , "addpath"		=> false
        , "title"		=> 'стата'
		, "description"	=> ''
        , "pageSize"	=> 0
        , "deleteStr"	=> LocaleLoader::Translate( "vt.publisher.deleteString")
    );
	
	$__breadcrumbs = array( array( 'link' => $grid['basepath'], 'title' => $__pageTitle ) );
?>
{increal:tmpl://vt/header.tmpl.php}
<div class="main">

		
		<!-- GRID -->
		{increal:tmpl://vt/elements/datagrid/header.tmpl.php}
<?php
    foreach ( $stat_summary as $k => $v )  {
//        $editpath   = $grid['basepath'] . "edit/" . $id;
?>
			<tr data-object-id="{$id}">
                <td class="header"><?=$k?></td>
                <td><?=$v['unique_users']?>(<?=$v['change_unq']?>)</td>
                <td><?=$v['all_users']?>(<?=$v['change_unuqunq']?>)</td>
                <td><?=$v['barters']?></td>
                <td><?=$v['barters_vis']?></td>
                <td><?=$v['barters_subs']?></td>

	        </tr>
<?php
    }
?>
		{increal:tmpl://vt/elements/datagrid/footer.tmpl.php}
		<!-- EOF GRID -->
	</div>
</div>
{increal:tmpl://vt/footer.tmpl.php}