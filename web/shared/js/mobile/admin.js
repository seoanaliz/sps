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

        $('#table').html(tmpl(TABLE));

        $('#table').delegate('.cell.decision .button', 'click', function() {
            var $button = $(this);
            var groupId = $button.closest('.row').data('group-id');

            if ($button.find('.approve').length) {
                t.approveGroup(groupId);
            }
            if ($button.find('.reject').length) {
                t.rejectGroup(groupId);
            }
        });
    },

    /**
     * @returns {Deferred}
     */
    decide: function(groupId, decision) {
        var deferred = new Deferred();

        switch (decision) {
            case MobileTable.DICISION_APPROVE:
                console.log(groupId, 'approve');
                break;
            case MobileTable.DICISION_REJECT:
                console.log(groupId, 'reject');
                break;
        }

        return deferred;
    },

    /**
     * @returns {Deferred}
     */
    approveGroup: function(groupId) {
        return this.decide(groupId, MobileTable.DICISION_APPROVE);
    },

    /**
     * @returns {Deferred}
     */
    rejectGroup: function(groupId) {
        return this.decide(groupId, MobileTable.DICISION_REJECT);
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
            'users: API.users.get({uids: "' + userIds.join(',') +  '", fields: "photo"}), ' +
            'groups: API.groups.getById({gids: "' + groupIds.join(',') + '"})' +
            '};';

            Control.callVKByOpenAPI('execute', {
                code: code
            }).success(function(data) {
                $.each(data.users, function(i, vkUser) {
                    var user = users[vkUser.uid];
                    user.photo = vkUser.photo;
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
MobileTable.DICISION_REJECT = 1;
MobileTable.DICISION_APPROVE = 2;

TABLE =
'<div class="header">' +
    '<div class="row">' +
        '<div class="column column3">' +
            '<div class="cell">Имя</div>' +
        '</div>' +
        '<div class="column column5">' +
            '<div class="cell">Паблики</div>' +
        '</div>' +
        '<div class="column column3">' +
            '<div class="cell">E-mail</div>' +
        '</div>' +
        '<div class="column column1">' +
            '<div class="cell">Решение</div>' +
        '</div>' +
    '</div>' +
'</div>' +
'<div class="body"></div>';

TABLE_BODY =
'<? if (count(items)) { ?>' +
    '<? each(TABLE_ROW, items); ?>' +
'<? } else { ?>' +
    '<div class="empty">Пусто</div>' +
'<? } ?>';

TABLE_ROW =
'<div class="row" data-group-id="<?=newUserRequestId?>">' +
    '<div class="column column3">' +
        '<div class="cell">' +
            '<div class="photo">' +
                '<img src="<?=photo?>" />' +
            '</div>' +
            '<div class="name">' +
                '<a href="http://vk.com/id<?=vkId?>" target="_blank"><?=name?></a>' +
            '</div>' +
        '</div>' +
    '</div>' +
    '<div class="column column5">' +
        '<div class="cell"><? each(GROUP, groups); ?></div>' +
    '</div>' +
    '<div class="column column3">' +
        '<div class="cell"><?=email?></div>' +
    '</div>' +
    '<div class="column column1">' +
        '<div class="cell decision">' +
            '<? if (statusId == MobileTable.STATUS_REVIEWING) { ?>' +
                '<button class="button">' +
                    '<span class="approve"></span>' +
                '</button> ' +
                '<button class="button">' +
                    '<span class="reject"></span>' +
                '</button>' +
            '<? } else { ?>' +
                '--' +
            '<? } ?>' +
        '</div>' +
    '</div>' +
'</div>';

GROUP =
'<div class="group">' +
    '<div class="photo">' +
        '<img src="<?=photo?>" />' +
    '</div>' +
    '<div class="name">' +
        '<a href="http://vk.com/public<?=id?>" target="_blank"><?=name?></a>' +
    '</div>' +
'</div>';