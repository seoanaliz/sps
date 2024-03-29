/**
 * @class GroupListWidget
 * @extends Widget
 */
GroupListWidget = Widget.extend({
    _template: REPORTS.GROUP_LIST,
    _modelClass: GroupListModel,
    _events: {
        'click: .item': 'clickItem',
        'keydown: input': 'keydownInput',
        'click: .delete' : 'deleteGroup'
    },

    _groupId: null,

    run: function() {
        var t = this;
        Control.fire('get_group_list', {}, function(data) {
            defaultGroupCollection.clear();
            sharedGroupCollection.clear();
            userGroupCollection.clear();
            $.each(data.default_list, function(i, group) {
                var groupModel = new GroupModel({
                    id: group.group_id,
                    name: group.name,
                    place: group.place,
                    type: group.type
                });
                defaultGroupCollection.add(groupModel.id(), groupModel);
            });
            $.each(data.shared_lists, function(i, group) {
                var groupModel = new GroupModel({
                    id: group.group_id,
                    name: group.name,
                    place: group.place,
                    type: group.type
                });
                sharedGroupCollection.add(groupModel.id(), groupModel);
            });
            $.each(data.user_lists, function(i, group) {
                var groupModel = new GroupModel({
                    id: group.group_id,
                    name: group.name,
                    place: group.place,
                    type: group.type
                });
                userGroupCollection.add(groupModel.id(), groupModel);
            });
            t.model().defaultLists(defaultGroupCollection);
            t.model().sharedLists(sharedGroupCollection);
            t.model().userLists(userGroupCollection);
            t.render();

            if (!t._groupId) {
                t.el().find('.item[data-id]:first').addClass('selected');
            } else {
                t.el().find('.item[data-id=' + t._groupId + ']').addClass('selected');
            }
            t._groupId = t.el().find('.item.selected').data('id');
        });
    },

    clickItem: function(e) {
        var t = this;
        var $target = $(e.target);
        var $list = $target.closest('.list');
        var $input = $list.find('input');

        var groupId = $target.data('id');
        if (groupId) {
            $input.hide();
            t.el().find('.item').removeClass('selected');
            $target.addClass('selected');
            t.trigger('change', groupId);
            t._groupId = groupId;
        } else {
            $input.show();
            $input.focus();
        }
    },

    deleteGroup: function(e) {

        var t = this;
        var $target = $(e.target);
        var $item   = $target.closest('.item');
        var groupId = $item.data('id');
        if (groupId) {
            Control.fire('delete_group', {
                groupId: groupId
            }, function() {
                t.run();
            });
        }
    },

    keydownInput: function(e) {
        var t = this;
        var $input = $(e.currentTarget);
        if (e.keyCode == KEY.ENTER) {
            Control.fire('add_group', {
                name: $input.val()
            }, function() {
                t.run();
            });
        }
    }
});
