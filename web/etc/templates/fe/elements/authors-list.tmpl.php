<?
    /**
     * @var $authors Author[]
     * @var $authorGroups Author[]
     * @var $userGroups UserGroup[]
     */
?>
<div class="authors-list">
    <div class="authors-types tab-bar">
        <div class="tab selected" data-type="authors">Авторы</div>
        <!-- <div class="tab" data-type="novice">Новички</div> -->
        <!-- <div class="tab" data-type="editors">Редакторы</div> -->
    </div>
    <div class="add-author">
        <div class="input-wrapper">
            <input type="text" class="author-link" placeholder="Введите ссылку на страницу пользователя" />
        </div>
    </div>
    <? if (!empty($authors)) { ?>
    <div class="list">
        <? foreach ($authors as $author) { ?>
            <div class="author" data-id="{$author->vkId}" data-group-ids="<?=implode(',', $authorGroups[$author->vkId])?>">
                <div class="photo">
                    <img src="{$author->avatar}" alt="" />
                </div>
                <div class="info">
                    <div class="name">
                        <a target="_blank" href="http://vk.com/id{$author->vkId}">{$author->FullName()}</a>
                    </div>
                </div>
                <div class="action add-to-list"></div>
                <div class="action delete"></div>
            </div>
        <? } ?>
    </div>
    <? } ?>
</div>

 <script style="text/javascript">
     userGroupCollection.clear();
     <? foreach ($userGroups as $UserGroup) {
        /** @var $UserGroup UserGroup */
         ?>
        var newUserGroupModel = new UserGroupModel();
        newUserGroupModel.name('<?=$UserGroup->name?>');
        newUserGroupModel.id(<?=$UserGroup->userGroupId?>);
        userGroupCollection.add(<?=$UserGroup->userGroupId?>, newUserGroupModel);
         <?
        }
    ?>
 </script>
