{increal:tmpl://st/elements/header.tmpl.php}
<div id="main" class="main">
    <div class="header">
        <div class="tab-bar">
            <span class="tab selected">Популярные</span>
            <span class="tab">Список</span>
        </div>
    </div>
    <div class="content">
        <table class="table">
            <thead>
                <tr>
                    <th class="public" width="30%">
                        <input class="filter" id="filter" type="text" placeholder="Поиск по названию" />
                    </th>
                    <th class="followers">
                        подписчики
                        <span class="icon arrow"></span>
                    </th>
                    <th class="growth">
                        прирост
                        <span class="icon arrow"></span>
                    </th>
                    <th class="contacts" width="31%">
                        контакты
                        <span class="icon arrow"></span>
                    </th>
                </tr>
            </thead>
            <tbody id="table-body"></tbody>
        </table>
    </div>
</div>
{increal:tmpl://st/elements/footer.tmpl.php}