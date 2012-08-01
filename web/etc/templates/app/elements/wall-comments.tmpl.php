<? if (true || !empty($comments)) { ?>
<div class="comments">
    <div class="list">
        {increal:tmpl://app/elements/wall-comments-list.tmpl.php}
    </div>
    <div class="new-comment">
        <div class="photo">
            <img src="http://vk.cc/Q2PuP" alt="" />
        </div>
        <div class="textarea-wrap">
            <textarea placeholder="Reply..." rows="1"></textarea>
        </div>
        <div class="actions">
            <button class="button send">Send</button>
            <span class="text">Ctrl+Enter</span>
        </div>
    </div>
</div>
<? } ?>