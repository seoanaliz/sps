<style>
    table
    {
        border-collapse:collapse;
    }
    table,th, td
    {
        border: 1px solid black;
        text-align: center;
    }
</style>

<form>
    Выберите дату начала: <input type="date" name="from">
    Выберите дату конца:  <input type="date" name="to">
    <input type="submit" value="Отправить"></p>
</form>

<?

//c1 - коэф. сложности
//c2 - коэф. репосты
//c3 - коэф. относительная крутизна
//c4 - коэф. оверпосты
header("Content-Type: text/html; charset=utf-8");
error_reporting(0);

$bablo_array = array();
$sum_publ = array();
$time_start = Request::getDateTime('from');
$time_start = $time_start ? $time_start->getTimestamp() : 0;
$time_stop  = Request::getDateTime('to');
$time_stop  = $time_stop ? $time_stop->getTimestamp() : 0;
$conf  = StatPublics::get_conf();
ob_start();
foreach( $our_publics as $public ) {
//        if ($public['id'] == 38000555 )
//            continue;
    ob_start();
    $diff_array_adms = array();
    $total = array();
    $q = 0;
    $admins = AdminsWork::get_public_admins( $time_start, $time_stop, $public );
    if( !$admins ) {
        ob_clean();
        continue;
    }
    ?>

<h3><a href="http://vk.com/public<?=$public['id']?>"> <?=$public['title']?></a></h3>
<table width="70%">
    <thead backgroun>
    <tr bgcolor="#8aaae1">
        <td width="25%">Админ</td>
        <td>Постов</td>
        <td>Сложных</td>
        <td>Средняя относительная оценка</td>
        <td>Репост/лайк</td>
        <td>Оверпостов</td>
        <td> вклад в ОД</td>
        <td>Список постов</td>
    </tr>
    </thead>
    <?

    foreach( $admins as $admin ) {
        if ( !in_array( $admin['id'], AdminsWork::$white_list ))
            continue;
        $q++;
        $posts = AdminsWork::get_posts( $admin['id'], $public['id'], $time_start, $time_stop);
        if(!$posts)
            continue;
        $quan = count($posts)-6;

        $total['posts']     += $quan;
        $total['compls']    += $posts['compls'];
        $total['diff_rel']  += $posts['diff_rel'];
        $total['reposts']   += $posts['reposts'];
        $total['overposts'] += $posts['overposts'];
        $total['rel_likes'] += $posts['rel_likes'];
        $lam = '@diff' . $admin['id'] . '@';
        $lam = ($public['id'] == 44438000555) ? '' : $lam;
        if ($public['id'] != 44438000555 ) {
            $diff_array_adms[$admin['id']] = ( $quan * ( 1 + $comps_per * $conf['c1_old'] + $repost_per * $conf['c2_old'] + $posts['diff_rel'] * $conf['c3_old'] - $overpost_per * $conf['c4_old'] ));
            $bablo_array[ $admin['id'] ] += $diff_array_adms[$admin['id']];
        }
        ?>
        <tr>
            <td><a href="http://vk.com/id<?=$admin['id'];?>"><img src="<?=$admin['ava']?>"/><br><?=$admin['name']?> </a></td>
            <td><?=$quan?></td>
            <td><?=round($posts['compls'] / $quan * 100 )?>%</td>
            <td><?=round( $posts['diff_rel'], 1 )?>%</td>
            <td><?=round( $posts['reposts'] / $posts['rel_likes'] * 100)?>%</td>
            <td><?=round( $posts['overposts'] / $quan * 100)?>% </td>
            <td><?=$lam?></td>
        </tr>
        <?
    }
    ?>
    <tr bgcolor="#fcdd76">

        <td>Итого</td>
        <td><?=$total['posts'];?></td>
        <td><?=$total['compls']?></td>
        <td><?=round( $total['diff_rel'] / $q )?>%</td>
        <td><?=round( $total['reposts'] / $total['rel_likes'] * 100 )?>%</td>
        <td><?=round( $total['overposts'] / $total['posts'] * 100 )?>% </td>

    </tr>
    </tbody>
</table><br>
<? if ($public['id'] != 44438000555 ) { ?>
    Всего паблик принес: <font color="red" size="4"><b> @<?=$public['id']?>@</b></font> руб.

    <br>
    <?
    }
    if ( $q ) {

        $ob = ob_get_contents();
        if ($public['id'] != 44438000555 ) {
            $sum = array_sum( $diff_array_adms );

            foreach ( $diff_array_adms as $k => $v ) {
                $ob = str_replace( "@diff$k@", round ( $v / $sum * 100 ) . '%', $ob );
                $full_table[ $public['id'] ][ $k ] = round ( $v / $sum, 3 );
            }

            $full_table[ $public['id'] ]['total_public_income'] =   $total['posts'] * ( 1 + $total['compls'] /$total['posts'] * $conf['c1_old'] +
                $total['reposts']   / $total['rel_likes']  * $conf['c2_old']  +
                $total['diff_rel'] / ( $q * 100 ) * $conf['c3_old'] -
                $total['overposts'] / $total['posts'] * $conf['c4_old'] );
        }
        $ob = str_replace( "@sum@" , round( $sum ), $ob );
        ob_clean();
        $a .= $ob;
        $ob = ob_get_contents();
        $sum = array_sum( $diff_array_adms );
    } else
        ob_clean();
}

//    echo '<font color="green" size="4">Total outcome:' . array_sum( $bablo_array ) . '</font><br>';
echo '<font color="red" size="22">++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font>';

$admins = AdminsWork::get_public_admins($time_start, $time_stop);
if ( !$admins )
    die('Нету постов!');
$ps = 0;
foreach( $full_table as $p )
{
    $ps += $p['total_public_income'];
}

foreach( $full_table as &$public ) {
    $public['total_public_income'] = $public['total_public_income'] / $ps * $conf['lval_old'];
}
unset($public);

$k = $full_table;
foreach($k as $key=>$value) {
    $a = str_replace( '@' . $key. '@', round($value['total_public_income']), $a );

}
echo $a;
foreach( $admins as $admin )
{
    if ( !in_array( $admin['id'], AdminsWork::$white_list ))
        continue;

    $t = 1;
    ob_start();
    ?>

<h3><a href="http://vk.com/id<?=$admin['id']?>"><img src="<?=$admin['ava']?>"/><br><?=$admin['name']?> </a></h3>
<table width="70%"  bordercolor="black" >
    <thead>
    <tr bgcolor="#8aaae1">
        <td width="25%">Паблик</td>
        <td>Постов</td>
        <td>Сложных</td>
        <td>Средняя относительная оценка</td>
        <td>Репост/лайк</td>
        <td>Оверпостов</td>
        <td>Список постов</td>
    </tr>
    </thead>

    <?
    $total = array();
    $q = 0;

    foreach( $our_publics as $public ) {
        if ($public['id'] == 44438000555 )
            continue;
        $posts = AdminsWork::get_posts($admin['id'], $public['id'], $time_start, $time_stop);
        if(!$posts)
            continue;
        $quan = count($posts)-6;
        $q++;
        $comps_per      = round( $posts['compls'] / $quan * 100 );
        $repost_per     = round( $posts['reposts'] / $posts['rel_likes'] * 100 );
        $overpost_per   = round( $posts['overposts'] / $quan * 100 );
        $diff_array_adms[$t] = round( $posts );
        ?>
            <tbody>
            <tr >
                <td><a href="http://vk.com/public<?=$public['id']?>"> <?=$public['title']?></a></td>
                <td><?=$quan?></td>
                <td><?=$comps_per?>%</td>
                <td><?=round($posts['diff_rel'])?>%</td>
                <td><?=$repost_per?>%</td>
                <td><?=$overpost_per?>%</td>

            </tr>
        <?
        $total['posts']     += $quan;
        $total['compls']    += $posts['compls'];
        $total['diff_rell'] += $posts['diff_rel'];
        $total['reposts']   += $posts['reposts'];
        $total['overposts'] += $posts['overposts'];
        $total['rel_likes'] += $posts['rel_likes'];
    }
    $final_salary = 0;

    foreach( $k as $v=>$public ) {

        if( isset( $public[ $admin['id']])) {
            $final_salary += $public[ $admin['id']] * $public['total_public_income'];
//                    echo "admin " . $admin['id'] . " public " . $v ." + " . $public['total_public_income'] . ' * ' .  $public[ $admin['id']] . '<br>';
        }
    }
    ?>

<tr bgcolor="#fcdd76">
    <td>Итого</td>
    <td><?=$total['posts']  ?></td>
    <td><?=$total['compls'] ?></td>
    <td><?=round( $total['diff_rell'] / $q, 1 ) ?>%</td>
    <td><?=round( $total['reposts'] / ( $total['rel_likes'] ) * 100 )?>%</td>
    <td><?=round( $posts['overposts'] / $total['posts'] * 100)?>%</td>
</tr>
</tbody>

    <font color="red" size="14">всего зп <?= round( $final_salary )?> руб.</font>
</table><br><br>

<?
    if ( $q )
        ob_end_flush();
    else
        ob_end_clean();
}

?>

{increal:tmpl://vt/footer.tmpl.php}