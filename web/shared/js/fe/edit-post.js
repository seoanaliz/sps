var SimpleEditPost = function(postId, $post, $el, data) {
    function setSelectionRange(input, selectionStart, selectionEnd) {
        if (input.setSelectionRange) {
            input.focus();
            input.setSelectionRange(selectionStart, selectionEnd);
        }
        else if (input.createTextRange) {
            var range = input.createTextRange();
            range.collapse(true);
            range.moveEnd('character', selectionEnd);
            range.moveStart('character', selectionStart);
            range.select();
        }
    }
    function setCaretToPos (input, pos) {
        setSelectionRange(input, pos, pos);
    }
    var onSave = function() {
        var text = $text.val();
        var link = data.link;
        var photos = $.parseJSON(data.photos);
        if (!($.trim(text) || link || photos.length)) {
            return $text.focus();
        } else {
            Events.fire("post", [
                text,
                photos,
                link,
                postId,
                function(data) {}
            ]);
        }
    };
    var onCancel = function() {
        $post.find('> .content').draggable('enable');
        $post.editing = false;
        $el.html(cache.html);
        $edit.remove();
    };

    var cache = {
        html: $el.html(),
        scroll: $(window).scrollTop()
    };
    $post.find('> .content').draggable('disable');
    $post.editing = true;
    //$el.html('');
    var $shortcut = $el.find('> .shortcut').hide();
    var $showFull = $el.find('> .show-cut').hide();
    var $edit = $('<div/>', {class: 'editing'}).prependTo($el);
    var $content = $('<div/>').appendTo($edit);
    var $text = $('<textarea/>').appendTo($content);

    if (true || data.text) {
        var text = data.text;
        $text
            .val(text.split('<br />').join('')) // because it's textarea
            .bind('keyup', function(e) {
                if (e.ctrlKey && e.keyCode == 13) { // Ctrl + Enter
                    onSave();
                }
                if (e.keyCode == 27) { // Esc
                    onCancel();
                }
            })
            .blur(function() {
                onCancel();
            })
            .autoResize()
            .keyup().focus();
        setCaretToPos($text.get(0), text.length);
    }
};
