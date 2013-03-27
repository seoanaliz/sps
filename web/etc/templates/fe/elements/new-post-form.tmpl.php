<div class="newpost collapsed bb">
    <div class="textarea-wrap">
        <textarea placeholder="Есть чем поделиться?"></textarea>
    </div>
    <div id="attach-file" class="buttons attach-file">
        <div class="save button l">Отправить</div>
        <a class="cancel l">Отменить</a>
        <!-- Штука для загрузки файла -->
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    function uploadCallback( data ) {
        t = $("#fileTemplate").tmpl( {title: '', filename: data.filename, isTemp: data.isTemp, path: data.image}, { counter: filesCounter } );
        $('.qq-upload-list').append(t);
        $(".qq-upload-success a.delete-attach").click(function(e){
            $(this).closest('li').remove();
            e.preventDefault();
        });
        $(".qq-upload-list li").each(function(){
            if (!$(this).attr('id')) {
                $(this).hide();
            }
        });
    }
</script>
<script id="fileTemplate" type="text/x-jquery-tmpl">
    <li class="qq-upload-success" id="file-${ $item.counter.nextIndex() }">
        <input type="hidden" name="files[${ $item.counter.index }][filename]" value="${filename}">
        <input type="hidden" name="files[${ $item.counter.index }][url]" value="${url}">
        <a href="javascript:;" class="delete-attach">удалить</a><img src="${path}" alt="" />
    </li>
</script><script>''</script>