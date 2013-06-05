<?php
/** @var StatUser[] $list */

$__pageTitle = LocaleLoader::Translate( "vt.screens.stat_user.list");

$grid = array(
    "columns" => array(
        LocaleLoader::Translate( "vt.stat_user.user_id" )
    , LocaleLoader::Translate( "vt.stat_user.name" )
    , LocaleLoader::Translate( "vt.stat_user.ava" )
    , LocaleLoader::Translate( "vt.stat_user.status" )
    )
, "colspans"   => array()
, "operations" => true
, "allowAdd"   => true
, "canPages"   => Stat_userFactory::CanPages()
, "basepath"   => Site::GetWebPath( "vt://stat_users/" )
, "title"      => $__pageTitle
, "pageSize"   => HtmlHelper::RenderToForm( $search["pageSize"] )
, "deleteStr"  => LocaleLoader::Translate( "vt.stat_user.deleteString")
);
?>
{increal:tmpl://vt/header.tmpl.php}
<div id="wrap">
    <div id="cont">
        <h1><?= ($grid["allowAdd"]) ? sprintf( "<span><a href=\"%sadd\">%s</a></span>", $grid["basepath"], LocaleLoader::Translate("vt.common.add") ) : "" ?> {$__pageTitle}</h1>
        <div class="blockEtc">
            <p class="blockHeader" title="{lang:vt.common.search}"><span><img src="{web:images://vt/find.png}" width="16" height="16" alt="" /> <strong>{lang:vt.common.search}</strong></span></p>
            <form id="searchForm" name="searchForm" method="post" action="{$grid[basepath]}" class="<?= $hideSearch == "true" ? "hidden" : ""  ?>">
                <input type="hidden" value="1" name="searchForm" />
                <input type="hidden" value="" id="pageId" name="page" />
                <input type="hidden" value="{$grid[pageSize]}" id="pageSize" name="search[pageSize]" />
                <table class="vertList" cellspacing="0">
                    <div class="row">
                        <label>{lang:vt.stat_user.user_id}</label>
                        <?= FormHelper::FormInput( "search[user_id]", $search['user_id'], 'user_id', null, array( 'size' => 80 ) ); ?>
                    </div>
                    <div class="row">
                        <label>{lang:vt.stat_user.name}</label>
                        <?= FormHelper::FormInput( "search[name]", $search['name'], 'name', null, array( 'size' => 80 ) ); ?>
                    </div>
                    <div class="row">
                        <label>{lang:vt.stat_user.ava}</label>
                        <?= FormHelper::FormInput( "search[ava]", $search['ava'], 'ava', null, array( 'size' => 80 ) ); ?>
                    </div>
                    <div class="row">
                        <label>{lang:vt.stat_user.comments}</label>
                        <?= FormHelper::FormInput( "search[comments]", $search['comments'], 'comments', null, array( 'size' => 80 ) ); ?>
                    </div>
                    <div class="row">
                        <label>{lang:vt.stat_user.rank}</label>
                        <?= FormHelper::FormInput( "search[rank]", $search['rank'], 'rank', null, array( 'size' => 80 ) ); ?>
                    </div>
<!--                    <div class="row">-->
<!--                        <label>{lang:vt.stat_user.access_token}</label>-->
<!--                        --><?//= FormHelper::FormInput( "search[access_token]", $search['access_token'], 'access_token', null, array( 'size' => 80 ) ); ?>
<!--                    </div>-->
<!--                    <div class="row">-->
<!--                        <label>{lang:vt.stat_user.mes_block_ts}</label>-->
<!--                        --><?//= FormHelper::FormInput( "search[mes_block_ts]", $search['mes_block_ts'], 'mes_block_ts', null, array( 'size' => 80 ) ); ?>
<!--                    </div>-->
                    <div class="row">
                        <label>{lang:vt.stat_user.status}</label>
                        <?= FormHelper::FormInput( "search[status]", $search['status'], 'status', null, array( 'size' => 80 ) ); ?>
                    </div>
                    <tr>
                        <th>&nbsp;</th>
                        <td><input name="find" type="submit" value="{lang:vt.common.find}" /></td>
                    </tr>
                </table>
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
            <tr class="lr" id="row_{$id}">
                <td>{$object.user_id}</td>
                <td>{$object.name}</td>
                <td>{$object.ava}</td>
                <td><?= ( !empty( $object->status ) ? $object->status->DefaultFormat() : '' ) ?></td>
                <td align="center">
                    <p class="control"><a class="edit" href="{$editpath}" title="{$langEdit}">{$langEdit}</a>&nbsp;<a class="delete" href="javascript:remove({$id});" title="{$langDelete}"><img src="{web:images://vt/icon_trash.png}" width="16" height="16" alt="{$langDelete}" /></a></p>
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