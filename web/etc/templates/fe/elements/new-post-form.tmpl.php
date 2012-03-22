<div class="newpost collapsed bb">
    <div class="input" contenteditable="true"></div>
    <div class="tip">Есть чем поделиться?</div>
    <div class="l" style="margin-top: 20px;">
        <input type="hidden" id="file_upload">
    </div>
    <div class="save button spr r">Сохранить</div>

    <div class="clear"></div>
</div>
<script type="text/javascript">
    var filesJSON = '';
    function uploadCallback( file,data ) {
        t = $("#fileTemplate").tmpl( {title: '', filename: data.filename, isTemp: data.isTemp, path: data.path, name : file.name}, { counter: filesCounter } );
        $('#' + file.id).replaceWith( t );
    }
</script>
<script id="fileTemplate" type="text/x-jquery-tmpl">
    <div class="uploadifyQueueItem sort" id="file-${ $item.counter.nextIndex() }">
        <input type="hidden" name="files[${ $item.counter.index }][filename]" value="${filename}">
        <img src="{web:images://vt/common/objects/sort-link.gif}" style="cursor: move;" class="handle">
        <div class="cancel"><a href="#" title="Удалить" class="delete-file"><img src="{web:js://ext/uploadify/uploadify-cancel.png}" border="0"></a></div>
        <span class="fileName">${filename}</span>
        <br /><br /><img src="${path}" alt="" />
        <br /><br /><input type="text" name="files[${ $item.counter.index }][title]" value="${title}" style="width: 98%"/>
    </div>
</script><script>''</script>