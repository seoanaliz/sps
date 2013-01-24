{increal:tmpl://vt/header.tmpl.php}
<div class="main">
    <div class="inner">
        <table class="objects">
            <thead>
                <tr>
                    <th class=""><span>Дата</span></th>
                    <th class="">&nbsp;</th>
                    <th class=""><span>Кол-во загруженных постов</span></th>
                    <th class=""><span>Кол-во загруженных авторских постов</span></th>
                    <th class=""><span>Кол-во загруженных постов из источников</span></th>
                    <th class="">&nbsp;</th>
                    <th class=""><span>Кол-во запланированных постов</span></th>
                    <th class=""><span>Кол-во отправленных постов</span></th>
                    <th class="">&nbsp;</th>
                    <th class=""><span>Кол-во ошибок импорта</span></th>
                    <th class=""><span>Кол-во ошибок экспорта</span></th>
                </tr>
            </thead>
            <tbody>
                <?
                    foreach ($stats as $date => $statsData) {
                        ?>
                        <tr>
                            <td class="header">{$date}</td>
                            <td>&nbsp;</td>
                            <td>{$statsData[totalArticlesCount]}</td>
                            <td>{$statsData[authorCreated]}</td>
                            <td>{$statsData[imported]}</td>
                            <td>&nbsp;</td>
                            <td>{$statsData[totalQueueCount]}</td>
                            <td>{$statsData[sent]}</td>
                            <td>&nbsp;</td>
                            <td>{$statsData[importErrors]}</td>
                            <td>{$statsData[exportErrors]}</td>
                        </tr>
                        <?
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>
{increal:tmpl://vt/footer.tmpl.php}