<div class="wall" id="wall">
    <div class="title clear-fix">
        <div class="text"></div>
        <div class="dropdown" style="visibility: hidden;">мои записи</div>
    </div>
    <div class="tabs">
        <div class="tab-bar">
            <div class="tab all selected">Все записи</div>
            <div class="tab planned">Запланированные</div>
            <div class="tab posted">Отправленные
                <spen class="counter">+1</spen>
            </div>
        </div>
    </div>
    <? if (!empty($targetFeeds)) { ?>
    <div class="new-post">
        <div class="textarea-wrap">
            <textarea placeholder="Есть чем поделиться?" rows="2"></textarea>
            <div class="add-photo"></div>
        </div>
        <div class="attachments">
            <div class="photos clear-fix"></div>
        </div>
        <div class="actions">
            <button class="button send">Отправить</button>
            <span class="text">Ctrl+Enter</span>
            <span class="file-uploader">Attach</span>
        </div>
    </div>
    <? } ?>
    <div class="list">

    </div>
</div>
