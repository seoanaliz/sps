<?
    /** @var $authors Author[] */
?>
<div class="users-editor">
    <div class="add-author">
        <div class="input-wrapper">
            <input type="text" class="author-link" placeholder="Введите ссылку на страницу пользователя" />
        </div>
    </div>
    <? if (!empty($authors)) { ?>
    <div class="authors-list">
        <? foreach ($authors as $author) { ?>
            <div class="author" data-id="{$author->vkId}">
                <div class="photo">
                    <img src="{$author->avatar}" alt="" />
                </div>
                <div class="info">
                    <div class="name">
                        <a target="_blank" href="http://vk.com/id{$author->vkId}">{$author->FullName()}</a>
                    </div>
                    <!-- div class="description">User description</div -->
                </div>
                <div class="delete"></div>
            </div>
        <? } ?>
    </div>
    <? } ?>
</div>