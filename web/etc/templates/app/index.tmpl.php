{increal:tmpl://app/elements/header.tmpl.php}
<div class="main clear-fix">
    <div class="left-column" id="left-column"></div>
    <div class="right-column" id="right-column">
        <div class="header">
            <div class="user-info clear-fix">
                <div class="photo">
                    <a target="_blank" href="http://vk.com/id{$__Author->vkId}">
                        <img src="{$__Author->avatar}" alt="" />
                    </a>
                </div>
                <div class="info">
                    <? if (!empty($__Author)) { ?>
                        <div class="title">
                            <a target="_blank" href="http://vk.com/id{$__Author->vkId}">{$__Author->FullName()}</a>
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
        {increal:tmpl://app/elements/menu.tmpl.php}
    </div>
</div>
{increal:tmpl://app/elements/footer.tmpl.php}
