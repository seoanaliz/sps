<?

    $c1 = Request::GetFloat('с1_new');
    $c2 = Request::GetFloat('с2_new');
    $c3 = Request::GetFloat('с3_new');
    $c4 = Request::GetFloat('с4_new');
    $lv = Request::GetInteger('lval_new');
    if ( $c1 && $c2 && $c3 && $c4 && $lv ) {
        StatPublics::save_conf($c1, $c2, $c3, $c4, $lv);
    }
    else {
        $conf = StatPublics::get_conf();
        $c1 = $conf['c1_old'];
        $c2 = $conf['c2_old'];
        $c3 = $conf['c3_old'];
        $c4 = $conf['c4_old'];
        $lv = $conf['lval_old'];
    }
?>

{increal:tmpl://im/elements/header.tmpl.php}
<form method="POST">
    Множитель для сложности поста: <input  name="с1_new" value="<?=$c1?>"><br>
    Множитель для репостов  : <input  name="с2_new" value="<?=$c2?>"><br>
    Множитель для крутизны(по лайкам): <input  name="с3_new" value="<?=$c3?>"><br>
    Множитель для оверпоста : <input  name="с4_new" value="<?=$c4?>"><br>
    Бабло                   : <input  name="lval_new" value="<?=$lv?>"><br>
    <input type="submit" value="Сохранить"></p>
</form>
{increal:tmpl://im/elements/footer.tmpl.php}

