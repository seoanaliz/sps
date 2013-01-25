Control = $.extend(Control, {
    root: Configs.controlsRoot,

    defaultParams: {
        type: 'barter'
    },

    commonResponse: function(data) {
        return data.response;
    },

    controlMap: {
        get_result_list: {
            name: 'getReportList',
            params: {
                filter: 'filter'
            },
            defaultParams: {
                state: 'complete'
            }
        },
        get_monitor_list: {
            name: 'getReportList',
            params: {
                filter: 'filter'
            }
        },
        delete_report: {
            name: 'deleteReport',
            defaultParams: {
                groupId: 0
            }
        },
        add_group: {
            name: 'setGroup',
            params: {
                name: 'groupName'
            }
        },
        add_report: {
            name: 'addReport',
            params: {
                ourPublicId: 'targetPublicId',
                publicId: 'barterPublicId',
                timestampStart: 'startTime',
                timestampStop: 'stopTime',
                groupId: 'groupId'
            },
            defaultParams: {
                timeShift: new Date().getTimezoneOffset()
            }
        },
        get_group_list: {
            name: 'getGroupList'
        },
        share_list: {
            name: 'shareGroup',
            params: {
                groupIds: 'groupId',
                userIds: 'recId'
            }
        },
        remove_list: {
            name: 'deleteGroup',
            params: {
                groupId: 'groupId'
            }
        }
    }
});
