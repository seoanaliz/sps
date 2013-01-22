Control = $.extend(Control, {
    root: Configs.controlsRoot,

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
        add_report: {
            name: 'addReport',
            params: {
                ourPublicId: 'targetPublicId',
                publicId: 'barterPublicId',
                timestampStart: 'startTime',
                timestampStop: 'stopTime'
            },
            defaultParams: {
                timeShift: new Date().getTimezoneOffset()
            }
        },
        get_group_list: {
            name: 'getGroupList',
            defaultParams: {
                type: 'barter'
            }
        }
    }
});
