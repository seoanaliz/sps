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
            defaultParams: {
                state: 'complete'
            }
        },
        delete_report: {
            name: 'deleteReport',
            defaultParams: {
                groupId: 0
            }
        },
        get_monitor_list: {
            name: 'getReportList'
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
        }
    }
});
