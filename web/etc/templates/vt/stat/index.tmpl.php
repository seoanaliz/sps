<?php
    /** @var Publisher[] $list */

    $__pageTitle = 'Стата';

    $grid = array(
        "columns" => array(
                'дата'
            ,   'уникальных подписчиков'
            ,   'аудитория'
            ,   'обмены'
            ,   'обмены, подп.'
            ,   'обмены, посет.'
        )
        , "colspans"	=> array()
        , "sorts"		=> array()
        , "operations"	=> false
        , "allowAdd"	=> false
        , "canPages"	=> false
        , "basepath"	=> Site::GetWebPath( "vt://stat/" )
        , "title"		=> 'стата'
		, "description"	=> ''
        , "pageSize"	=> 0
    );
	
	$__breadcrumbs = array( array( 'link' => $grid['basepath'], 'title' => $__pageTitle ) );
?>
{increal:tmpl://vt/header.tmpl.php}
<div class="main">
<a href="?id=0">Все</a>
<a href="?id=196506553">Володя</a>
<!-- GRID -->
{increal:tmpl://vt/elements/datagrid/header.tmpl.php}
<?php
    foreach ( $stat_summary as $k => $v )  {
?>
			<tr>
                <td class="header"><?=$k?></td>
                <td><?=$v['unique_users']?>(<?=$v['change_unq']?>)</td>
                <td><?=$v['all_users']?>(<?=$v['change_unuqunq']?>)</td>
                <td><?=$v['barters']?></td>
                <td><?=$v['barters_subs']?></td>
                <td><?=$v['barters_vis']?></td>

	        </tr>
<?php
    }
?>
		{increal:tmpl://vt/elements/datagrid/footer.tmpl.php}
		<!-- EOF GRID -->
	</div>
</div>
{increal:tmpl://vt/footer.tmpl.php}