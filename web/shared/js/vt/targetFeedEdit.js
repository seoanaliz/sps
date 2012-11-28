/** @var editors */
$(function(){
    $( "input.userList" )
        // don't navigate away from the field on tab when selecting an item
        .bind( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).data( "autocomplete" ).menu.active ) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 0,
            source: function( request, response ) {
                var term = request.term;
                var findVkId = (parseInt(term) + '') == term;
                var results = [];
                for (var vkId in editors) {
                    var name = editors[vkId];

                    if (findVkId && vkId.indexOf(term) == 0) {
                        results.push({ label: vkId + ' (' + name + ')', value: vkId, name: name })
                    }

                    if (name.indexOf(term) > -1) {
                        results.push({ label: name + ' (' + vkId + ')', value: vkId, name: name})
                    }
                }
                response(results);
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var b = $('#u' + ui.item.value);
                if (b.length) {
                   b.parent().remove();
                }
                $(this).val('');
                var list = $('#' + this.id + '_values');
                list.append('<li>' +
                        '<input type="hidden" name="' + list.data('input_name') + '" value="' +  ui.item.value + '">' +
                        ui.item.name  +
                        '<span class="button remove" id="u' + ui.item.value + '" onclick="removeUser(this)"></span>' +
                    '</li>');
                return false;
            }

        })
});

function removeUser(button){
    console.log(button)
    $(button)
}