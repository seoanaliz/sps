/**
 * Counters for album index
 */
var Counter = function() {
    this.index = -1;
}

Counter.prototype.nextIndex = function() {
    this.index ++;
    return this.index;
}

var filesCounter = new Counter();

$(document).ready(function() {
    $('#file_upload').uploadify({
        'uploader'        : root + 'int/controls/image-upload/',
        'swf'             : root + 'shared/js/ext/uploadify/uploadify.swf',
        'langFile'        : root + 'shared/js/ext/uploadify/uploadifyLang_en.js',
        'cancelImage'     : root + 'shared/js/ext/uploadify/uploadify-cancel.png',
        'method'          : 'post',
        'auto'            : true,
        'multi'           : true,
        'buttonText'      : 'Прикрепить',
        'rollover'    : true,
        'width'           : 139,
        'height'    : 30,
        'checkExisting'   : false,
        'onUploadSuccess' : function(file,data,response) {
            result = JSON.parse( data );
            if( result ) {
                if( result.error ) {
                    popupError( result.error );
                } else {
                    result.isTemp = true;

                    uploadCallback( file, result );

                    $('.uploadifyQueue').sortable({
                        'items': '.uploadifyQueueItem'
                        , 'forceHelperSize': true
                        , 'forcePlaceholderSize': true
                        , 'handle': '.handle'
                    });
                }
            }
        }
    });

    $('.uploadifyQueue').sortable({
        'items': '.uploadifyQueueItem'
        , 'forceHelperSize': true
        , 'forcePlaceholderSize': true
        , 'handle': '.handle'
    });
});