{increal:tmpl://app/elements/header.tmpl.php}
<div class="main clear-fix">
    <div class="left-column" id="left-column">
        <div class="header">
            <div class="user-info clear-fix">
                <div class="photo">
                    <a target="_blank" href="http://vk.com/id{$__Author->vkId}">
                        <img src="{$__Author->avatar}" alt="" />
                    </a>
                </div>
                <div class="info">
                    <div class="title">
                        <a target="_blank" href="http://vk.com/id{$__Author->vkId}">{$__Author->FullName()}</a>
                    </div>
                    <!--div class="other">
                        <span class="rating"><span class="icon"></span> 300</span>
                        <span class="likes"><span class="icon hart"></span> 15000</span>
                        <span class="diff">+1300</span>
                    </div-->
                </div>
            </div>
        </div>
        {increal:tmpl://app/elements/wall-posts.tmpl.php}
    </div>
    <div class="right-column" id="right-column">
        {increal:tmpl://app/elements/menu.tmpl.php}
    </div>
</div>
{increal:tmpl://app/elements/footer.tmpl.php}