{increal:tmpl://vt/header.tmpl.php}

<script type="text/javascript">
    function setPrice(a){
        var $input  =   a.find('input');
        var $price  =   Number($input.val());
        var $id     =   $(a).prop('id');

        if (isNaN($price) || $price == 0){return;}

//        alert(controlsRoot + 'setPrice/');
            $.ajax({
                    url: controlsRoot + 'setPrice/',
                    type: 'GET',
//                    dataType : "json",
                    data: {
                        publId: $id,
                        price: $price
                    },
                    success: function (data) {
                        alert('ok');
                    }
            });
    };


</script>
 <table id="tbld" class="tablesorter" bordercolor="black" border="1"  >
    <thead>
    <tr>
        <th>Паблик</th>
        <th>Название</th>
        <th> <?=Response::getInteger('last_time'); ?> </th>
        <th>Процент роста</th>
        <th>Абсолютный рост</th>
        <th>Администраторы</th>
    </tr>
    </thead>
    <tbody>;


     <?


             $button_form = '<br>
                       <div id="%1$d" class="price" >
                            <br> %2$d р.<br>
                            <input type="text" size="10"> р.<br>
                            <button type="button" onclick="javascript:setPrice($(this).parent());" >Послать</button>
                       </div>
             ';

    for($i = 1; $i <= $pages; $i++)
    {
        echo '<a href="' . '?page=' . $i . '">' . $i . '</a>  ';
        echo '  ';
    }
    echo '<br>';
    echo '<br>';

        foreach($big_publics_array as $publ => $dt) {
            $price = $dt['price'];
            $time_last = end($dt['quantity']);
            $time_comparison = prev($dt['quantity']);
            if(!$time_comparison)
                $time_comparison = $time_last;
            if (!$time_comparison || !$time_last) continue;
            echo '<tr>';
            $img = "<img src=\"". $dt['ava'] . "\" href=\"http://vk.com/public$publ\"/>";
            $ad_line = '';

            //формирование ячейки админов
            if (count($dt['admins']) > 0) {
                foreach ($dt['admins'] as $admin) {
                    $ad_line .= "<a href=\"http://vk.com/id"
                             . $admin['vk_id'] . "\"><img width=50 src=\""
                             . $admin['ava']. "\" title=\"" .  $admin['name']. " "
                             . $admin['role'] . "\"  /></a>"

                             ."<br>";
                }
            }

            $ad_line = $ad_line ? $ad_line : 'Данные отсутствуют';

            $name = "<a  href=\"http://vk.com/public$publ\">" . $dt['name'] . "</a>";
            echo "<td >$img<td>$name  <td> $time_last<td>" . round($time_last*100/$time_comparison-100, 2) . '%<td>' .( $time_last - $time_comparison).'<td>'
                . $ad_line . sprintf($button_form, $publ, $dt['price']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
           echo '<br>';
           echo '<br>';

        for($i = 1; $i <= $pages; $i++)
        {
            echo '<a href="' . '?page=' . $i . '">' . $i . '</a> ';
            echo '  ';
        }
?>
{increal:tmpl://vt/footer.tmpl.php}