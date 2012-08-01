<div class="comments">
    <div class="list">
        {increal:tmpl://app/elements/wall-comments-list.tmpl.php}
    </div>
    <div class="new-comment">
        <div class="photo">
            <img src="{$__Author->avatar}" alt="" />
        </div>
        <div class="textarea-wrap">
            <textarea placeholder="Ваш текст..." rows="1"></textarea>
        </div>
        <div class="actions">
            <button class="button send">Отправить</button>
            <span class="text">Ctrl+Enter</span>
        </div>
    </div>
</div>