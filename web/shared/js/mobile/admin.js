$(document).ready(function() {
    Control.root = controlsRoot;
    window.app = new MobileTable();
});

var MobileTable = Class.extend({
    init: function() {
        var t = this;

        t.showReviewingRequests().always(function() {
            $('#global-loader').fadeOut();
        });

        $('#reviewing').mousedown(function() {
            t.showReviewingRequests();
        });

        $('#approved').mousedown(function() {
            t.showApprovedRequests();
        });

        $('#rejected').mousedown(function() {
            t.showRejectedRequests();
        });
    },

    /**
     * @returns {Deferred}
     */
    loadRequests: function(status) {
        var deferred = new Deferred();

        Control.call('getNewUsers', {
            status: status,
            limit: 100,
            offset: 0
        }).success(function(data) {
            var users = {};
            var groups = {};
            var userIds = [];
            var groupIds = [];

            $.each(data, function(id, user) {
                if (typeof user.publicIds == 'string') {
                    user.publicIds = user.publicIds.split(',');
                }
                userIds.push(user.vkId);

                $.each(user.publicIds, function(i, id) {
                    groups[id] = groups[id] ? groups[id] + 1 : 1;

                    if (groups[id] == 1) {
                        groupIds.push(id);
                    }
                });

                users[user.vkId] = user;
            });

            var code = 'return {' +
            'users: API.users.get({uids: "' + userIds.join(',') +  '"}), ' +
            'groups: API.groups.getById({gids: "' + groupIds.join(',') + '"})' +
            '};';

            Control.callVKByOpenAPI('execute', {
                code: code
            }).success(function(data) {
                $.each(data.users, function(i, vkUser) {
                    var user = users[vkUser.uid];
                    user.name = vkUser.first_name + ' ' + vkUser.last_name;
                    user.groups = [];

                    for (var publicId in user.publicIds) {
                        user.groups.push({
                            id: data.groups[publicId].gid,
                            photo: data.groups[publicId].photo,
                            name: data.groups[publicId].name
                        });
                    }
                });
                deferred.fireSuccess(users);
            }).error(function() {
                deferred.fireError();
            });
        }).error(function() {
            deferred.fireError();
        });

        return deferred;
    },

    /**
     * @returns {Deferred}
     */
    showRequests: function(status) {
        $('#main > .header').find('.tab').removeClass('selected');
        var $tab;

        switch(status) {
            case MobileTable.STATUS_REVIEWING:
                $tab = $('#reviewing');
                break;
            case MobileTable.STATUS_APPROVED:
                $tab = $('#approved');
                break;
            case MobileTable.STATUS_REJECTED:
                $tab = $('#rejected');
                break;
        }

        if ($tab) {
            $tab.addClass('selected');
        }

        return this.loadRequests(status).success(function(users) {
            $('#table > .body').html(tmpl(TABLE_BODY, {items: users}));
        });
    },

    /**
     * @returns {Deferred}
     */
    showReviewingRequests: function() {
        return this.showRequests(MobileTable.STATUS_REVIEWING);
    },

    /**
     * @returns {Deferred}
     */
    showApprovedRequests: function() {
        return this.showRequests(MobileTable.STATUS_APPROVED);
    },

    /**
     * @returns {Deferred}
     */
    showRejectedRequests: function() {
        return this.showRequests(MobileTable.STATUS_REJECTED);
    }
});

MobileTable.STATUS_REVIEWING = 1;
MobileTable.STATUS_APPROVED = 4;
MobileTable.STATUS_REJECTED = 5;

TABLE_BODY =
'<? each(TABLE_ROW, items); ?>';

TABLE_ROW =
'<div class="row">' +
    '<div class="column column3">' +
        '<?=name?>' +
    '</div>' +
    '<div class="column column7">' +
        '<? each(GROUP, groups); ?>' +
    '</div>' +
    '<div class="column column2">' +
        'Да Нет' +
    '</div>' +
'</div>';

GROUP =
'<?=name?><br/>';