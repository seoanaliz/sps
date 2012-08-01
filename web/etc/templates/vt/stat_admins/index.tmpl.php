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

    $time_start = Request::getDateTime('from');
    $time_start = $time_start ? $time_start->getTimestamp() : 0 ;
    $time_stop  = Request::getDateTime('to');
    $time_stop  = $time_stop ? $time_stop->getTimestamp() : 0 ;

    foreach( $our_publics as $public )
    {
        $admins = AdminsWork::get_public_admins($time_start, $time_stop, $public);
        if( !$admins )
            continue;
     ?>

        <h3><a href="http://vk.com/public<?=$public['id']?>"> <?=$public['title']?></a></h3>
        <table width="70%">
        <thead backgroun>
        <tr bgcolor="#8aaae1">
            <td width="25%">Админ</td>
            <td>Постов</td>
            <td>Топиков</td>
            <td>Сложных</td>
            <td>Репост/пост</td>
            <td>Лайк/пост</td>
            <td>Список постов</td>
        </tr>
        </thead>
    <?

        foreach( $admins as $admin ) {

            $posts = AdminsWork::get_posts($admin['id'], $public['id'], $time_start, $time_stop);
            if(!$posts)
                continue;
            ?>
            <tr>
            <td><a href="http://vk.com/id<?=$admin['id'];?>"><img src="<?=$admin['ava']?>"/><br><?=$admin['name']?> </a></td>
            <td><?=count($posts)-4;?></td>
            <td><?=$posts['topics']?></td>
            <td><?=$posts['compls']?></td>
            <td><?=$posts['reposts']?></td>
            <td><?=$posts['rel_likes']?></td>
            <td>
            <?
//                unset ($posts[topics]);
//                unset ($posts[compls]);
//                unset ($posts[reposts]);
//                unset ($posts[rel_likes]);
//            foreach($posts as $post) {
//                ?>
<!--                    <a href="http://vk.com/wall-"--><?//=$public['id']?><!--_--><?//=$post?><!-->--><?//=$public['id']?><!--_--><?//=$post?><!--</a>-->
<!---->
<!---->
<!--                --><?//
//            }
            ?>
            </td></tr>
            <?
        }
        ?>
        </table><br><br>
        <?
    }
    echo '<font color="red" size="22">++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font>';
    $admins = AdminsWork::get_public_admins($time_start, $time_stop);
    if (!$admins)
        die('Нету постов!');
    foreach( $admins as $admin )
    {
        ?>
        <h3><a href="http://vk.com/id<?=$admin['id'];?>"><img src="<?=$admin['ava']?>"/><br><?=$admin['name']?> </a></h3>
        <table width="70%"  bordercolor="black" >
            <thead>
            <tr bgcolor="#8aaae1">
                <td width="25%">Паблик</td>
                <td>Постов</td>
                <td>Топиков</td>
                <td>Сложных</td>
                <td>Репост/пост</td>
                <td>Лайк/пост</td>
                <td>Список постов</td>
            </tr>
            </thead>

        <?
        $total = array();
        $q = 0;
        foreach( $our_publics as $public ) {

            $posts = AdminsWork::get_posts($admin['id'], $public['id'], $time_start, $time_stop);
            if(!$posts)
                continue;
            $q++;
            ?>
            <tbody>
            <tr >
                <td><a href="http://vk.com/public<?=$public['id']?>"> <?=$public['title']?></a></td>
                <td><?=count($posts)-4;?></td>
                <td><?=$posts['topics']?></td>
                <td><?=$posts['compls']?></td>
                <td><?=$posts['reposts']?></td>
                <td><?=$posts['rel_likes']?></td>
                <td>
                    <?
    //                unset ($posts[topics]);
    //                unset ($posts[compls]);
    //                unset ($posts[reposts]);
    //                unset ($posts[rel_likes]);
    //            foreach($posts as $post) {
    //                ?>
                    <!--                    <a href="http://vk.com/wall-"--><?//=$public['id']?><!--_--><?//=$post?><!-->--><?//=$public['id']?><!--_--><?//=$post?><!--</a>-->
                    <!---->
                    <!---->
                    <!--                --><?//
    //            }
                    ?>
                </td></tr>
            <?
            $total['posts']     += count($posts)-4;
            $total['topics']    += $posts['topics'];
            $total['compls']    += $posts['compls'];
            $total['reposts']   += $posts['reposts'];
            $total['rel_likes'] += $posts['rel_likes'];

        }


        ?>

            <tr bgcolor="#fcdd76">
            <td>Итого</td>
            <td><?=$total['posts'];?></td>
            <td><?=$total['topics']?></td>
            <td><?=$total['compls']?></td>
            <td><?=$total['reposts']/$q?></td>
            <td><?=$total['rel_likes']/$q?></td>
            <td>
            </td></tr>
            </tbody>
        </table><br><br>
<?
    }

?>

{increal:tmpl://vt/footer.tmpl.php}