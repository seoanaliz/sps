<div class="wall" id="wall">
    <div class="title clear-fix">
        <div class="text"></div>
        <div class="dropdown">мои записи</div>
    </div>
    <? if (!empty($targetFeeds)) { ?>
    <div class="new-post">
        <div class="textarea-wrap">
            <textarea placeholder="Ваш текст..." rows="2"></textarea>
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
