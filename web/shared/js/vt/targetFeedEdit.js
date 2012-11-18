/** @var editors */
$(function(){
    $( "input.editors" )
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
                $(this).val('');
                var list = $('#' + this.id + '_values');
                list.append('<li>' +
                        '<input type="hidden" name="' + list.data('input_name') + '" value="' +  ui.item.value + '">' +
                        ui.item.name  +
                        '<span class="button remove" onclick="removeUser(this)"></span>' +
                    '</li>');
                return false;
            }

        })
});

function removeUser(button){
    $(button).parent().remove()
}